<?php

declare(strict_types=1);

namespace ZxArt\Tests\PictureSearch;

use ApiQueriesManager;
use ApiQuery;
use authorElement;
use controller;
use LanguagesManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\AuthorList\AuthorListTransformer;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\PictureSearch\Dto\PictureSearchQuery;
use ZxArt\PictureSearch\PictureSearchOrder;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\PictureSearch\PictureSearchService;
use ZxArt\PictureSearch\PictureSearchSort;
use ZxArt\Pictures\PicturesTransformer;
use ZxArt\Shared\EntityType;

#[AllowMockObjectsWithoutExpectations]
class PictureSearchServiceTest extends TestCase
{
    private ApiQuery&MockObject $apiQuery;
    private AuthorListTransformer&MockObject $authorListTransformer;
    private PictureSearchService $service;

    /** @var array<string, mixed>|null */
    private ?array $capturedParameters = null;
    private ?string $capturedExportType = null;
    /** @var array<string, string>|null */
    private ?array $capturedOrder = null;

    /** @var array<string, mixed> */
    private array $queryResult = ['zxPicture' => [], 'totalAmount' => 0];

    protected function setUp(): void
    {
        $this->apiQuery = $this->createMock(ApiQuery::class);
        $this->apiQuery->method('setFiltrationParameters')->willReturnCallback(function (array $parameters) {
            $this->capturedParameters = $parameters;
            return $this->apiQuery;
        });
        $this->apiQuery->method('setExportType')->willReturnCallback(function (string $exportType) {
            $this->capturedExportType = $exportType;
            return $this->apiQuery;
        });
        $this->apiQuery->method('setOrder')->willReturnCallback(function (array $order) {
            $this->capturedOrder = $order;
            return $this->apiQuery;
        });
        $this->apiQuery->method('getQueryResult')->willReturnCallback(fn() => $this->queryResult);

        $apiQueriesManager = $this->createMock(ApiQueriesManager::class);
        $apiQueriesManager->method('getQuery')->willReturn($this->apiQuery);

        $languagesManager = $this->createMock(LanguagesManager::class);
        $languagesManager->method('getCurrentLanguageCode')->willReturn('eng');

        $controller = $this->createMock(controller::class);
        $controller->baseURL = 'https://zxart.ee/';

        $this->authorListTransformer = $this->createMock(AuthorListTransformer::class);

        $this->service = new PictureSearchService(
            $apiQueriesManager,
            $this->createMock(PicturesTransformer::class),
            $this->authorListTransformer,
            $languagesManager,
            $this->createMock(structureManager::class),
            $controller,
        );
    }

    public function testSearchMapsFiltersToLegacyFiltrationParameters(): void
    {
        $this->service->search($this->makeQuery(
            titleWord: 'flame',
            startYear: 1996,
            endYear: 1995,
            minRating: 4.0,
            minPartyPlace: 3,
            pictureType: 'standard',
            realtimeOnly: true,
            inspirationOnly: true,
            stagesOnly: true,
            tagsInclude: ['girl', 'portrait'],
            tagsExclude: ['3d'],
            authorCountryIds: [10],
            authorCityIds: [20, 21],
        ));

        $this->assertSame([
            'zxPictureTitleSearch' => 'flame',
            'zxPictureYear' => [1995, 1996],
            'zxPictureMinPartyPlace' => 3,
            'zxPictureMinRating' => 4.0,
            'zxPictureType' => 'standard',
            'zxPictureCompo' => ['realtime', 'realtimep'],
            'zxPictureInspiration' => true,
            'zxPictureStages' => true,
            'zxPictureTagsInclude' => ['girl', 'portrait'],
            'zxPictureTagsExclude' => ['3d'],
            'authorCountry' => [10],
            'authorCity' => [20, 21],
        ], $this->capturedParameters);
        $this->assertSame('zxPicture', $this->capturedExportType);
        $this->assertSame(['date' => 'desc'], $this->capturedOrder);
    }

    public function testSearchWithoutPictureFiltersFallsBackToAllPictures(): void
    {
        $this->service->search($this->makeQuery(authorCountryIds: [10]));

        $this->assertSame([
            'zxPictureAll' => true,
            'authorCountry' => [10],
        ], $this->capturedParameters);
    }

    public function testStartYearOnlyExpandsRangeToCurrentYear(): void
    {
        $currentYear = (int)date('Y');
        $this->service->search($this->makeQuery(startYear: $currentYear - 2));

        $this->assertSame(
            range($currentYear - 2, $currentYear),
            $this->capturedParameters['zxPictureYear'] ?? null,
        );
    }

    public function testAuthorsResultsTypeExportsAuthorsAndTransformsThem(): void
    {
        $authorDto = new AuthorListItemDto(
            id: 5,
            url: 'https://zxart.ee/eng/authors/a/acme/',
            entityType: EntityType::Author,
            title: 'Acme',
            realName: '',
            realNameUrl: null,
            groups: [],
            countryId: null,
            countryTitle: null,
            countryUrl: null,
            cityId: null,
            cityTitle: null,
            cityUrl: null,
            musicRating: 0.0,
            graphicsRating: 1.5,
        );
        $authorElement = $this->createMock(authorElement::class);
        $this->authorListTransformer->method('authorToDto')->with($authorElement)->willReturn($authorDto);
        $this->queryResult = ['author' => [$authorElement], 'totalAmount' => 1];

        $result = $this->service->search($this->makeQuery(resultsType: PictureSearchResultsType::Authors));

        $this->assertSame('author', $this->capturedExportType);
        $this->assertSame('authorPicture', $this->capturedParameters['authorOfItemType'] ?? null);
        $this->assertSame(1, $result->totalAmount);
        $this->assertSame([], $result->pictures);
        $this->assertSame([$authorDto], $result->authors);
    }

    public function testSearchBuildsLegacyCompatibleApiAndZipUrls(): void
    {
        $result = $this->service->search($this->makeQuery(
            titleWord: 'flame',
            tagsInclude: ['girl', 'portrait'],
        ));

        $this->assertSame(
            'https://zxart.ee/api/types:zxPicture/export:zxPicture/language:eng/start:0/limit:60/order:date,desc/'
            . 'filter:zxPictureTitleSearch=flame;zxPictureTagsInclude=girl,portrait;',
            $result->apiUrl,
        );
        $this->assertSame(
            'https://zxart.ee/zipItems/export:zxPicture/language:eng/structure:authors/'
            . 'filter:zxPictureTitleSearch=flame;zxPictureTagsInclude=girl,portrait;',
            $result->zipUrl,
        );
    }

    /**
     * @param string[] $tagsInclude
     * @param string[] $tagsExclude
     * @param int[] $authorCountryIds
     * @param int[] $authorCityIds
     */
    private function makeQuery(
        ?string $titleWord = null,
        ?int $startYear = null,
        ?int $endYear = null,
        ?float $minRating = null,
        ?int $minPartyPlace = null,
        ?string $pictureType = null,
        bool $realtimeOnly = false,
        bool $inspirationOnly = false,
        bool $stagesOnly = false,
        array $tagsInclude = [],
        array $tagsExclude = [],
        array $authorCountryIds = [],
        array $authorCityIds = [],
        PictureSearchResultsType $resultsType = PictureSearchResultsType::Items,
    ): PictureSearchQuery {
        return new PictureSearchQuery(
            titleWord: $titleWord,
            startYear: $startYear,
            endYear: $endYear,
            minRating: $minRating,
            minPartyPlace: $minPartyPlace,
            pictureType: $pictureType,
            realtimeOnly: $realtimeOnly,
            inspirationOnly: $inspirationOnly,
            stagesOnly: $stagesOnly,
            tagsInclude: $tagsInclude,
            tagsExclude: $tagsExclude,
            authorCountryIds: $authorCountryIds,
            authorCityIds: $authorCityIds,
            resultsType: $resultsType,
            sortParameter: PictureSearchSort::Date,
            sortOrder: PictureSearchOrder::Desc,
            start: 0,
            limit: 60,
        );
    }
}
