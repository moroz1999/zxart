<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\Search\FoundGroupsDto;
use ZxArt\Assistant\Tools\Search\SearchGroupsRequestDto;
use ZxArt\Assistant\Tools\Search\SearchGroupsTool;
use ZxArt\Groups\Dto\FoundGroupDto;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Groups\Services\FoundGroupsReader;
use ZxArt\Groups\Services\GroupSearchService;
use ZxArt\Shared\Dto\IdPageDto;

final class SearchGroupsToolTest extends TestCase
{
    public function testItIsTheReadToolNamedGroups(): void
    {
        $tool = new SearchGroupsTool($this->createStub(GroupSearchService::class), $this->createStub(FoundGroupsReader::class));

        self::assertInstanceOf(ReadTool::class, $tool);
        self::assertSame('search_groups', $tool->name());
        self::assertSame(SearchGroupsRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersThePageTheSearchFoundWithTheSummariesOfItsIds(): void
    {
        $filter = new GroupSearchFilterDto(name: 'Sage');
        $summaries = [new FoundGroupDto(id: 7, title: 'Sage', abbreviation: '', type: 'scene', country: null, city: null, aliases: [])];
        $search = $this->createMock(GroupSearchService::class);
        $search->expects(self::once())->method('search')->with($filter, 40, 20)->willReturn(new IdPageDto(total: 41, ids: [7]));
        $reader = $this->createMock(FoundGroupsReader::class);
        $reader->expects(self::once())->method('read')->with([7])->willReturn($summaries);

        $result = (new SearchGroupsTool($search, $reader))->execute(new SearchGroupsRequestDto($filter, limit: 20, offset: 40));

        self::assertEquals(new FoundGroupsDto(total: 41, results: $summaries), $result);
    }

    public function testTheLimitIsKeptBetweenOneAndAHundred(): void
    {
        $search = $this->createMock(GroupSearchService::class);
        $search->expects(self::exactly(2))->method('search')
            ->with(self::anything(), 0, self::callback(static fn(int $limit): bool => $limit === 1 || $limit === 100))
            ->willReturn(new IdPageDto(total: 0, ids: []));
        $reader = $this->createStub(FoundGroupsReader::class);
        $reader->method('read')->willReturn([]);
        $tool = new SearchGroupsTool($search, $reader);

        $tool->execute(new SearchGroupsRequestDto(new GroupSearchFilterDto(), limit: 0));
        $tool->execute(new SearchGroupsRequestDto(new GroupSearchFilterDto(), limit: 500));
    }
}
