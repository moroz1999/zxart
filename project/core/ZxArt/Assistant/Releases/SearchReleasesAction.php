<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Releases\Services\FoundReleasesReader;
use ZxArt\Releases\Services\ReleaseSearchService;

/**
 * Finds releases by typed filters, one page at a time.
 *
 * @psalm-api
 */
final readonly class SearchReleasesAction implements ReadAction
{
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
    public function schema(): string
    {
        return SearchReleasesArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): FoundReleasesDto
    {
        $search = SearchReleasesArgumentsDto::from($arguments);
        $page = $this->releaseSearchService->search($search->filter, $search->page->offset, $search->page->limit);

        return new FoundReleasesDto(total: $page->total, results: $this->foundReleasesReader->read($page->ids));
    }
}
