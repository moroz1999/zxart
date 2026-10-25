<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use ApiQueriesManager;
use ApiQuery;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\Prods\Dbal\ProdSearchDbal;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Releases\ReleaseTypes;

class ProdSearchServiceTest extends TestCase
{
    public function testTheCatalogueFiltersGoToTheApiQueryAndTheFilterToTheDbal(): void
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
            'zxProdTagsInclude' => [7],
        ]);
        $apiQuery->expects($this->once())->method('setTemporaryTables')->with(false);
        $apiQuery->method('getExportFilteredQuery')->willReturn($catalogue);
        $filter = new ProdSearchFilterDto(
            title: 'Star',
            categoryIds: [92159],
            yearFrom: 1983,
            hardware: ['zx48'],
            languages: ['en'],
            legalStatuses: [LegalStatus::allowed],
            releaseTypes: [ReleaseTypes::original],
            formats: ['tap'],
            authorIds: [5],
            countryIds: [47450],
            tagIds: [7],
        );
        $dbal = $this->createMock(ProdSearchDbal::class);
        $dbal->expects($this->once())->method('count')->with($catalogue, $filter)->willReturn(12);
        $dbal->expects($this->once())->method('findIds')->with($catalogue, $filter, 10, 5)->willReturn([3, 4]);

        $result = $this->service($apiQuery, $dbal)->search($filter, 10, 5);

        self::assertSame(12, $result->total);
        self::assertSame([3, 4], $result->ids);
    }

    public function testWithoutCatalogueFiltersEveryProductionIsSearched(): void
    {
        $apiQuery = $this->createMock(ApiQuery::class);
        $apiQuery->expects($this->once())->method('setFiltrationParameters')->with(['zxProdAll' => true]);
        $apiQuery->method('getExportFilteredQuery')->willReturn($this->createStub(Builder::class));
        $dbal = $this->createStub(ProdSearchDbal::class);
        $dbal->method('findIds')->willReturn([]);

        $this->service($apiQuery, $dbal)->search(new ProdSearchFilterDto(title: 'Elite'), 0, 10);
    }

    public function testTheIdsQueryIsWhatAnotherSearchNarrowsBy(): void
    {
        $catalogue = $this->createStub(Builder::class);
        $idsQuery = $this->createStub(Builder::class);
        $apiQuery = $this->createStub(ApiQuery::class);
        $apiQuery->method('getExportFilteredQuery')->willReturn($catalogue);
        $filter = new ProdSearchFilterDto(yearFrom: 1985);
        $dbal = $this->createMock(ProdSearchDbal::class);
        $dbal->expects($this->once())->method('idsQuery')->with($catalogue, $filter)->willReturn($idsQuery);

        self::assertSame($idsQuery, $this->service($apiQuery, $dbal)->idsQuery($filter));
    }

    public function testACatalogueThatMatchesNothingFindsNothing(): void
    {
        $apiQuery = $this->createStub(ApiQuery::class);
        $apiQuery->method('getExportFilteredQuery')->willReturn(null);
        $dbal = $this->createMock(ProdSearchDbal::class);
        $dbal->expects($this->never())->method('findIds');

        $result = $this->service($apiQuery, $dbal)->search(new ProdSearchFilterDto(categoryIds: [1]), 0, 10);

        self::assertSame(0, $result->total);
        self::assertSame([], $result->ids);
        self::assertNull($this->service($apiQuery, $dbal)->idsQuery(new ProdSearchFilterDto(categoryIds: [1])));
    }

    private function service(ApiQuery $apiQuery, ProdSearchDbal $dbal): ProdSearchService
    {
        $apiQueriesManager = $this->createStub(ApiQueriesManager::class);
        $apiQueriesManager->method('getQuery')->willReturn($apiQuery);

        return new ProdSearchService($apiQueriesManager, $dbal);
    }
}
