<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\Groups\Dbal\GroupSearchDbal;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Groups\Services\GroupSearchService;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\Services\ProdSearchService;

class GroupSearchServiceTest extends TestCase
{
    public function testWithoutASoftwareFilterTheDbalSearchesAlone(): void
    {
        $filter = new GroupSearchFilterDto(name: 'Triumph', types: ['scene']);
        $dbal = $this->createMock(GroupSearchDbal::class);
        $dbal->expects($this->once())->method('count')->with($filter, null)->willReturn(7);
        $dbal->expects($this->once())->method('findIds')->with($filter, null, 20, 10)->willReturn([4, 5]);
        $prodSearch = $this->createMock(ProdSearchService::class);
        $prodSearch->expects($this->never())->method('idsQuery');

        $page = (new GroupSearchService($dbal, $prodSearch))->search($filter, 20, 10);

        self::assertSame(7, $page->total);
        self::assertSame([4, 5], $page->ids);
    }

    public function testTheSoftwareFilterNarrowsByTheSoftwareItFinds(): void
    {
        $prods = new ProdSearchFilterDto(yearFrom: 1985);
        $filter = new GroupSearchFilterDto(prods: $prods);
        $found = $this->createStub(Builder::class);
        $prodSearch = $this->createMock(ProdSearchService::class);
        $prodSearch->expects($this->once())->method('idsQuery')->with($prods)->willReturn($found);
        $dbal = $this->createMock(GroupSearchDbal::class);
        $dbal->expects($this->once())->method('count')->with($filter, $found)->willReturn(1);
        $dbal->expects($this->once())->method('findIds')->with($filter, $found, 0, 10)->willReturn([4]);

        self::assertSame([4], (new GroupSearchService($dbal, $prodSearch))->search($filter, 0, 10)->ids);
    }

    public function testSoftwareThatCannotBeFoundFindsNoGroups(): void
    {
        $prodSearch = $this->createStub(ProdSearchService::class);
        $prodSearch->method('idsQuery')->willReturn(null);
        $dbal = $this->createMock(GroupSearchDbal::class);
        $dbal->expects($this->never())->method('findIds');

        $page = (new GroupSearchService($dbal, $prodSearch))
            ->search(new GroupSearchFilterDto(prods: new ProdSearchFilterDto(categoryIds: [1])), 0, 10);

        self::assertSame(0, $page->total);
    }
}
