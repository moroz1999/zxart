<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Search;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\Search\FoundReleasesDto;
use ZxArt\Assistant\Tools\Search\SearchReleasesRequestDto;
use ZxArt\Assistant\Tools\Search\SearchReleasesTool;
use ZxArt\Releases\Dto\FoundReleaseDto;
use ZxArt\Releases\Dto\ReleaseSearchFilterDto;
use ZxArt\Releases\Services\FoundReleasesReader;
use ZxArt\Releases\Services\ReleaseSearchService;
use ZxArt\Shared\Dto\IdPageDto;

final class SearchReleasesToolTest extends TestCase
{
    public function testItIsTheReadToolNamedReleases(): void
    {
        $tool = new SearchReleasesTool($this->createStub(ReleaseSearchService::class), $this->createStub(FoundReleasesReader::class));

        self::assertInstanceOf(ReadTool::class, $tool);
        self::assertSame('search_releases', $tool->name());
        self::assertSame(SearchReleasesRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersThePageTheSearchFoundWithTheSummariesOfItsIds(): void
    {
        $filter = new ReleaseSearchFilterDto(title: 'Elite');
        $summaries = [new FoundReleaseDto(id: 7, title: 'Elite', year: 1985, prod: null, releaseType: 'original', formats: ['tap'], fileName: 'elite.tap', authors: [], publishers: [])];
        $search = $this->createMock(ReleaseSearchService::class);
        $search->expects(self::once())->method('search')->with($filter, 40, 20)->willReturn(new IdPageDto(total: 41, ids: [7]));
        $reader = $this->createMock(FoundReleasesReader::class);
        $reader->expects(self::once())->method('read')->with([7])->willReturn($summaries);

        $result = (new SearchReleasesTool($search, $reader))->execute(new SearchReleasesRequestDto($filter, limit: 20, offset: 40));

        self::assertEquals(new FoundReleasesDto(total: 41, results: $summaries), $result);
    }

    public function testTheLimitIsKeptBetweenOneAndAHundred(): void
    {
        $search = $this->createMock(ReleaseSearchService::class);
        $search->expects(self::exactly(2))->method('search')
            ->with(self::anything(), 0, self::callback(static fn(int $limit): bool => $limit === 1 || $limit === 100))
            ->willReturn(new IdPageDto(total: 0, ids: []));
        $reader = $this->createStub(FoundReleasesReader::class);
        $reader->method('read')->willReturn([]);
        $tool = new SearchReleasesTool($search, $reader);

        $tool->execute(new SearchReleasesRequestDto(new ReleaseSearchFilterDto(), limit: 0));
        $tool->execute(new SearchReleasesRequestDto(new ReleaseSearchFilterDto(), limit: 500));
    }
}
