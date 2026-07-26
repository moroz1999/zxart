<?php

declare(strict_types=1);

namespace ZxArt\Tests\PictureSearch;

use authorElement;
use controller;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\AuthorList\AuthorListTransformer;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\PictureSearch\Dto\PictureSearchQuery;
use ZxArt\PictureSearch\PictureSearchOrder;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\PictureSearch\PictureSearchService;
use ZxArt\PictureSearch\PictureSearchSort;
use ZxArt\PictureSearch\Repositories\PictureSearchRepository;
use ZxArt\Pictures\PicturesTransformer;
use ZxArt\Pictures\Services\PicturesManager;
use ZxArt\Shared\EntityType;

#[AllowMockObjectsWithoutExpectations]
class PictureSearchServiceTest extends TestCase
{
    private AuthorListTransformer&MockObject $authorListTransformer;
    private AuthorsService&MockObject $authorsService;
    private PictureSearchService $service;

    /** Author elements the authors branch resolves. */
    private array $authorElements = [];

    protected function setUp(): void
    {
        // The filtration parameters are no longer handed to a query object: they
        // only reach the outside world through the api/zip URLs, so the tests read
        // them back from there.
        $countQuery = $this->createMock(Builder::class);
        $countQuery->method('count')->willReturn(1);

        $repository = $this->createMock(PictureSearchRepository::class);
        $repository->method('buildPicturesQuery')->willReturn($countQuery);
        $repository->method('buildAuthorsQuery')->willReturn($countQuery);

        $picturesManager = $this->createMock(PicturesManager::class);
        $picturesManager->method('getElementsByQuery')->willReturn([]);

        $this->authorsService = $this->createMock(AuthorsService::class);
        $this->authorsService->method('getElementsByQuery')
            ->willReturnCallback(fn(): array => $this->authorElements);

        $languagesManager = $this->createMock(LanguagesManager::class);
        $languagesManager->method('getCurrentLanguageCode')->willReturn('eng');

        $controller = $this->createMock(controller::class);
        $controller->baseURL = 'https://zxart.ee/';

        $this->authorListTransformer = $this->createMock(AuthorListTransformer::class);

        $this->service = new PictureSearchService(
            $repository,
            $picturesManager,
            $this->authorsService,
            $this->createMock(PicturesTransformer::class),
            $this->authorListTransformer,
            $languagesManager,
            $this->createMock(structureManager::class),
            $controller,
        );
    }

    /** The `filter:` segment of the api URL, as `name=value` pairs. */
    private function filterSegment(string $apiUrl): string
    {
        $position = strpos($apiUrl, 'filter:');
        return $position === false ? '' : substr($apiUrl, $position + strlen('filter:'));
    }

    public function testSearchMapsFiltersToLegacyFiltrationParameters(): void
    {
        $result = $this->service->search($this->makeQuery(
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

        $this->assertSame(
            'zxPictureTitleSearch=flame;zxPictureYear=1995,1996;zxPictureMinPartyPlace=3;zxPictureMinRating=4;'
            . 'zxPictureType=standard;zxPictureCompo=realtime,realtimep;zxPictureInspiration=1;zxPictureStages=1;'
            . 'zxPictureTagsInclude=girl,portrait;zxPictureTagsExclude=3d;authorCountry=10;authorCity=20,21;',
            $this->filterSegment($result->apiUrl),
        );
        $this->assertStringContainsString('types:zxPicture/export:zxPicture/', $result->apiUrl);
        $this->assertStringContainsString('order:date,desc/', $result->apiUrl);
    }

    public function testSearchWithoutPictureFiltersFallsBackToAllPictures(): void
    {
        $result = $this->service->search($this->makeQuery(authorCountryIds: [10]));

        $this->assertSame('zxPictureAll=1;authorCountry=10;', $this->filterSegment($result->apiUrl));
    }

    public function testStartYearOnlyExpandsRangeToCurrentYear(): void
    {
        $currentYear = (int)date('Y');
        $result = $this->service->search($this->makeQuery(startYear: $currentYear - 2));

        $this->assertSame(
            'zxPictureYear=' . implode(',', range($currentYear - 2, $currentYear)) . ';',
            $this->filterSegment($result->apiUrl),
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
            realNameId: null,
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
        $this->authorElements = [$authorElement];

        $result = $this->service->search($this->makeQuery(resultsType: PictureSearchResultsType::Authors));

        $this->assertStringContainsString('types:author/export:author/', $result->apiUrl);
        $this->assertStringContainsString('authorOfItemType=authorPicture;', $result->apiUrl);
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
        bool $fromGameOnly = false,
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
            fromGameOnly: $fromGameOnly,
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
