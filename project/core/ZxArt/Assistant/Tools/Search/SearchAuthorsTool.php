<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Authors\Services\FoundAuthorsReader;
use ZxArt\Authors\Services\AuthorSearchService;

/**
 * @implements ReadTool<SearchAuthorsRequestDto>
 *
 * @psalm-api
 */
final readonly class SearchAuthorsTool implements ReadTool
{
    private const int MAX_LIMIT = 100;

    public function __construct(
        private AuthorSearchService $authorSearchService,
        private FoundAuthorsReader $foundAuthorsReader,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'search_authors';
    }

    #[Override]
    public function description(): string
    {
        return 'Finds authors by typed filters, every filter given must hold: a part of the nickname, real name or an '
            . 'alias, countries, cities, groups they are members of, active years (the years of the software they '
            . 'made or published), productions given by id or found by a nested production filter, how they are '
            . 'linked to them (made or published) and the roles of their credit, co-authors of an author. What an '
            . 'alias did counts for its author. Answers one page by nickname and the total; each author with the '
            . 'real name, country, city, aliases and groups.';
    }

    #[Override]
    public function requestClass(): string
    {
        return SearchAuthorsRequestDto::class;
    }

    #[Override]
    public function execute(object $request): FoundAuthorsDto
    {
        $limit = min(max(1, $request->limit), self::MAX_LIMIT);
        $page = $this->authorSearchService->search($request->filter, max(0, $request->offset), $limit);

        return new FoundAuthorsDto(total: $page->total, results: $this->foundAuthorsReader->read($page->ids));
    }
}
