<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use ApiQueriesManager;
use ApiQuery;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\Prods\Dbal\ProdSearchRepository;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Releases\ReleaseTypes;

class ProdSearchServiceTest extends TestCase
{
    public function testTheCatalogueFiltersGoToTheApiQueryAndTheRestToTheRepository(): void
    {
        $catalogue = $this->createStub(Builder::class);
        $apiQuery = $this->createMock(ApiQuery::class);
        $apiQuery->expects($this->once())->method('setExportType')->with('zxProd');
        $apiQuery->expects($this->once())->method('setFiltrationParameters')->with([
            'zxProdCategory' => [92159],
            'zxProdHardware' => ['zx48'],
            'zxProdLanguage' => ['en'],
            'zxProdStatus' => ['allowed'],
            'zxReleaseReleaseType' => ['original'],
            'zxReleaseFormat' => ['tap'],
            'zxProdCountryId' => [47450],
            'zxProdTagsInclude' => [7],
        ]);
        $apiQuery->expects($this->once())->method('setTemporaryTables')->with(false);
        $apiQuery->method('getExportFilteredQuery')->willReturn($catalogue);
        $repository = $this->createMock(ProdSearchRepository::class);
        $repository->expects($this->once())->method('count')
            ->with($catalogue, 'Star', 1983, 1986, 5, 6)->willReturn(12);
        $repository->expects($this->once())->method('findIds')
            ->with($catalogue, 'Star', 1983, 1986, 5, 6, 10, 5)->willReturn([3, 4]);

        $result = $this->service($apiQuery, $repository)->search(new ProdSearchFilterDto(
            title: ' Star ',
            categoryIds: [92159],
            yearFrom: 1983,
            yearTo: 1986,
            hardware: ['zx48'],
            languages: ['en'],
            legalStatuses: [LegalStatus::allowed],
            releaseTypes: [ReleaseTypes::original],
            formats: ['tap'],
            authorId: 5,
            groupId: 6,
            countryIds: [47450],
            tagIds: [7],
        ), 10, 5);

        self::assertSame(12, $result->total);
        self::assertSame([3, 4], $result->ids);
    }

    public function testWithoutCatalogueFiltersEveryProductionIsSearched(): void
    {
        $apiQuery = $this->createMock(ApiQuery::class);
        $apiQuery->expects($this->once())->method('setFiltrationParameters')->with(['zxProdAll' => true]);
        $apiQuery->method('getExportFilteredQuery')->willReturn($this->createStub(Builder::class));
        $repository = $this->createStub(ProdSearchRepository::class);
        $repository->method('findIds')->willReturn([]);

        $this->service($apiQuery, $repository)->search(new ProdSearchFilterDto(title: 'Elite'), 0, 10);
    }

    private function service(ApiQuery $apiQuery, ProdSearchRepository $repository): ProdSearchService
    {
        $apiQueriesManager = $this->createStub(ApiQueriesManager::class);
        $apiQueriesManager->method('getQuery')->willReturn($apiQuery);

        return new ProdSearchService($apiQueriesManager, $repository);
    }
}
