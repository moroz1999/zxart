<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\Search\FoundProdsDto;
use ZxArt\Assistant\Tools\Search\SearchProdsRequestDto;
use ZxArt\Assistant\Tools\Search\SearchProdsTool;
use ZxArt\Prods\Dto\FoundProdDto;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\Services\FoundProdsReader;
use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Shared\Dto\IdPageDto;

final class SearchProdsToolTest extends TestCase
{
    public function testItIsTheReadToolNamedProds(): void
    {
        $tool = new SearchProdsTool($this->createStub(ProdSearchService::class), $this->createStub(FoundProdsReader::class));

        self::assertInstanceOf(ReadTool::class, $tool);
        self::assertSame('search_prods', $tool->name());
        self::assertSame(SearchProdsRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersThePageTheSearchFoundWithTheSummariesOfItsIds(): void
    {
        $filter = new ProdSearchFilterDto(title: 'Elite');
        $summaries = [new FoundProdDto(id: 7, title: 'Elite', year: 1985, authors: [], groups: [], publishers: [], categories: [])];
        $search = $this->createMock(ProdSearchService::class);
        $search->expects(self::once())->method('search')->with($filter, 40, 20)->willReturn(new IdPageDto(total: 41, ids: [7]));
        $reader = $this->createMock(FoundProdsReader::class);
        $reader->expects(self::once())->method('read')->with([7])->willReturn($summaries);

        $result = (new SearchProdsTool($search, $reader))->execute(new SearchProdsRequestDto($filter, limit: 20, offset: 40));

        self::assertEquals(new FoundProdsDto(total: 41, results: $summaries), $result);
    }

    public function testTheLimitIsKeptBetweenOneAndAHundred(): void
    {
        $search = $this->createMock(ProdSearchService::class);
        $search->expects(self::exactly(2))->method('search')
            ->with(self::anything(), 0, self::callback(static fn(int $limit): bool => $limit === 1 || $limit === 100))
            ->willReturn(new IdPageDto(total: 0, ids: []));
        $reader = $this->createStub(FoundProdsReader::class);
        $reader->method('read')->willReturn([]);
        $tool = new SearchProdsTool($search, $reader);

        $tool->execute(new SearchProdsRequestDto(new ProdSearchFilterDto(), limit: 0));
        $tool->execute(new SearchProdsRequestDto(new ProdSearchFilterDto(), limit: 500));
    }
}
