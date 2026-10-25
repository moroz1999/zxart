<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Releases\Services\FoundReleasesReader;
use ZxArt\Releases\Services\ReleaseSearchService;

/**
 * @implements ReadTool<SearchReleasesRequestDto>
 *
 * @psalm-api
 */
final readonly class SearchReleasesTool implements ReadTool
{
    private const int MAX_LIMIT = 100;

    public function __construct(
        private ReleaseSearchService $releaseSearchService,
        private FoundReleasesReader $foundReleasesReader,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'search_releases';
    }

    #[Override]
    public function description(): string
    {
        return 'Finds releases by typed filters, every filter given must hold: title, file name, the md5 of the release '
            . 'file or of a file inside it, productions given by id or found by a nested production filter, year, '
            . 'release type, formats, required hardware, languages, publishers (authors or groups) and credited '
            . 'authors with their roles. Answers one page by title and the total; each release with its production, '
            . 'year, type, formats, file name, authors and publishers.';
    }

    #[Override]
    public function requestClass(): string
    {
        return SearchReleasesRequestDto::class;
    }

    #[Override]
    public function execute(object $request): FoundReleasesDto
    {
        $limit = min(max(1, $request->limit), self::MAX_LIMIT);
        $page = $this->releaseSearchService->search($request->filter, max(0, $request->offset), $limit);

        return new FoundReleasesDto(total: $page->total, results: $this->foundReleasesReader->read($page->ids));
    }
}
