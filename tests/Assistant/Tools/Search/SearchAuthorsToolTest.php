<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\Search\FoundAuthorsDto;
use ZxArt\Assistant\Tools\Search\SearchAuthorsRequestDto;
use ZxArt\Assistant\Tools\Search\SearchAuthorsTool;
use ZxArt\Authors\Dto\FoundAuthorDto;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Authors\Services\FoundAuthorsReader;
use ZxArt\Authors\Services\AuthorSearchService;
use ZxArt\Shared\Dto\IdPageDto;

final class SearchAuthorsToolTest extends TestCase
{
    public function testItIsTheReadToolNamedAuthors(): void
    {
        $tool = new SearchAuthorsTool($this->createStub(AuthorSearchService::class), $this->createStub(FoundAuthorsReader::class));

        self::assertInstanceOf(ReadTool::class, $tool);
        self::assertSame('search_authors', $tool->name());
        self::assertSame(SearchAuthorsRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersThePageTheSearchFoundWithTheSummariesOfItsIds(): void
    {
        $filter = new AuthorSearchFilterDto(name: 'Alex');
        $summaries = [new FoundAuthorDto(id: 7, title: 'Alex', realName: '', country: null, city: null, aliases: [], groups: [])];
        $search = $this->createMock(AuthorSearchService::class);
        $search->expects(self::once())->method('search')->with($filter, 40, 20)->willReturn(new IdPageDto(total: 41, ids: [7]));
        $reader = $this->createMock(FoundAuthorsReader::class);
        $reader->expects(self::once())->method('read')->with([7])->willReturn($summaries);

        $result = (new SearchAuthorsTool($search, $reader))->execute(new SearchAuthorsRequestDto($filter, limit: 20, offset: 40));

        self::assertEquals(new FoundAuthorsDto(total: 41, results: $summaries), $result);
    }

    public function testTheLimitIsKeptBetweenOneAndAHundred(): void
    {
        $search = $this->createMock(AuthorSearchService::class);
        $search->expects(self::exactly(2))->method('search')
            ->with(self::anything(), 0, self::callback(static fn(int $limit): bool => $limit === 1 || $limit === 100))
            ->willReturn(new IdPageDto(total: 0, ids: []));
        $reader = $this->createStub(FoundAuthorsReader::class);
        $reader->method('read')->willReturn([]);
        $tool = new SearchAuthorsTool($search, $reader);

        $tool->execute(new SearchAuthorsRequestDto(new AuthorSearchFilterDto(), limit: 0));
        $tool->execute(new SearchAuthorsRequestDto(new AuthorSearchFilterDto(), limit: 500));
    }
}
