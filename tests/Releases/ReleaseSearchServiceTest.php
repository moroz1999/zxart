<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Releases\Dbal\ReleaseSearchDbal;
use ZxArt\Releases\Dto\ReleaseSearchFilterDto;
use ZxArt\Releases\Services\ReleaseSearchService;

class ReleaseSearchServiceTest extends TestCase
{
    public function testWithoutASoftwareFilterTheDbalSearchesAlone(): void
    {
        $filter = new ReleaseSearchFilterDto(title: 'Elite');
        $prodSearch = $this->createMock(ProdSearchService::class);
        $prodSearch->expects($this->never())->method('idsQuery');
        $dbal = $this->createMock(ReleaseSearchDbal::class);
        $dbal->expects($this->once())->method('count')->with($filter, null)->willReturn(7);
        $dbal->expects($this->once())->method('findIds')->with($filter, null, 5, 2)->willReturn([1, 2]);

        $page = (new ReleaseSearchService($dbal, $prodSearch))->search($filter, 5, 2);

        self::assertSame(7, $page->total);
        self::assertSame([1, 2], $page->ids);
    }

    public function testTheProductionsTheNestedSearchFindsNarrowTheReleases(): void
    {
        $prods = new ProdSearchFilterDto(yearFrom: 1985);
        $filter = new ReleaseSearchFilterDto(prods: $prods);
        $found = $this->createStub(Builder::class);
        $prodSearch = $this->createMock(ProdSearchService::class);
        $prodSearch->expects($this->once())->method('idsQuery')->with($prods)->willReturn($found);
        $dbal = $this->createMock(ReleaseSearchDbal::class);
        $dbal->expects($this->once())->method('count')->with($filter, $found)->willReturn(1);
        $dbal->expects($this->once())->method('findIds')->with($filter, $found, 0, 10)->willReturn([3]);

        self::assertSame([3], (new ReleaseSearchService($dbal, $prodSearch))->search($filter, 0, 10)->ids);
    }

    public function testANestedSearchThatCanFindNothingAnswersAnEmptyPage(): void
    {
        $prodSearch = $this->createStub(ProdSearchService::class);
        $prodSearch->method('idsQuery')->willReturn(null);
        $dbal = $this->createMock(ReleaseSearchDbal::class);
        $dbal->expects($this->never())->method('findIds');

        $page = (new ReleaseSearchService($dbal, $prodSearch))->search(new ReleaseSearchFilterDto(prods: new ProdSearchFilterDto()), 0, 10);

        self::assertSame(0, $page->total);
        self::assertSame([], $page->ids);
    }
}
