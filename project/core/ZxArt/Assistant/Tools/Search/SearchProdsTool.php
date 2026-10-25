<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Prods\Services\FoundProdsReader;
use ZxArt\Prods\Services\ProdSearchService;

/**
 * @implements ReadTool<SearchProdsRequestDto>
 *
 * @psalm-api
 */
final readonly class SearchProdsTool implements ReadTool
{
    private const int MAX_LIMIT = 100;

    public function __construct(
        private ProdSearchService $prodSearchService,
        private FoundProdsReader $foundProdsReader,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'search_prods';
    }

    #[Override]
    public function description(): string
    {
        return 'Finds productions (software) by typed filters, every filter given must hold: title, categories, years of '
            . 'the production, of its releases or when it was added, hardware, languages, legal statuses, release '
            . 'types and formats, credited authors (all of them, or any with anyAuthor) and their roles, groups that '
            . 'made or published it, publishers (authors or groups), the country or city of its authors or developer '
            . 'groups, party, compo and place, series and compilations, tags. Countries, cities, authors and groups '
            . 'are given by id: find them first. Answers one page by title and the total; each production with its '
            . 'year, authors and roles, groups, publishers and categories.';
    }

    #[Override]
    public function requestClass(): string
    {
        return SearchProdsRequestDto::class;
    }

    #[Override]
    public function execute(object $request): FoundProdsDto
    {
        $limit = min(max(1, $request->limit), self::MAX_LIMIT);
        $page = $this->prodSearchService->search($request->filter, max(0, $request->offset), $limit);

        return new FoundProdsDto(total: $page->total, results: $this->foundProdsReader->read($page->ids));
    }
}
