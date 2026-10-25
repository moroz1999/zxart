<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\Authors\Dbal\AuthorSearchDbal;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Authors\Services\AuthorSearchService;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\Services\ProdSearchService;

class AuthorSearchServiceTest extends TestCase
{
    public function testWithoutASoftwareFilterTheDbalSearchesAlone(): void
    {
        $filter = new AuthorSearchFilterDto(name: 'Alex', cityIds: [2]);
        $dbal = $this->createMock(AuthorSearchDbal::class);
        $dbal->expects($this->once())->method('count')->with($filter, null)->willReturn(7);
        $dbal->expects($this->once())->method('findIds')->with($filter, null, 20, 10)->willReturn([4, 5]);
        $prodSearch = $this->createMock(ProdSearchService::class);
        $prodSearch->expects($this->never())->method('idsQuery');

        $page = (new AuthorSearchService($dbal, $prodSearch))->search($filter, 20, 10);

        self::assertSame(7, $page->total);
        self::assertSame([4, 5], $page->ids);
    }

    public function testTheSoftwareFilterNarrowsByTheSoftwareItFinds(): void
    {
        $prods = new ProdSearchFilterDto(categoryIds: [92179], yearFrom: 1985, yearTo: 1987);
        $filter = new AuthorSearchFilterDto(prodRoles: ['music'], prods: $prods);
        $found = $this->createStub(Builder::class);
        $prodSearch = $this->createMock(ProdSearchService::class);
        $prodSearch->expects($this->once())->method('idsQuery')->with($prods)->willReturn($found);
        $dbal = $this->createMock(AuthorSearchDbal::class);
        $dbal->expects($this->once())->method('count')->with($filter, $found)->willReturn(1);
        $dbal->expects($this->once())->method('findIds')->with($filter, $found, 0, 10)->willReturn([4]);

        self::assertSame([4], (new AuthorSearchService($dbal, $prodSearch))->search($filter, 0, 10)->ids);
    }

    public function testSoftwareThatCannotBeFoundFindsNoAuthors(): void
    {
        $prodSearch = $this->createStub(ProdSearchService::class);
        $prodSearch->method('idsQuery')->willReturn(null);
        $dbal = $this->createMock(AuthorSearchDbal::class);
        $dbal->expects($this->never())->method('findIds');

        $page = (new AuthorSearchService($dbal, $prodSearch))
            ->search(new AuthorSearchFilterDto(prods: new ProdSearchFilterDto(categoryIds: [1])), 0, 10);

        self::assertSame(0, $page->total);
        self::assertSame([], $page->ids);
    }
}
