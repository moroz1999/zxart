<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Prods\Services\FoundProdsReader;
use ZxArt\Prods\Services\ProdSearchService;

/**
 * Finds productions by typed filters, one page at a time.
 *
 * @psalm-api
 */
final readonly class SearchProdsAction implements ReadAction
{
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
            . 'the production or of its releases, hardware, languages, legal statuses, release types and formats, '
            . 'credited authors (all of them, or any with anyAuthor) and their roles, groups that made or published it, '
            . 'publishers (authors or groups), the country or city of its authors or developer groups, party, compo and '
            . 'place, series and compilations, tags. Countries, cities, authors and groups are given by id: find them '
            . 'first. Answers one page by title and the total; each production with its year, authors and roles, '
            . 'groups, publishers and categories.';
    }

    #[Override]
    public function schema(): string
    {
        return SearchProdsArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): FoundProdsDto
    {
        $search = SearchProdsArgumentsDto::from($arguments);
        $page = $this->prodSearchService->search($search->filter, $search->page->offset, $search->page->limit);

        return new FoundProdsDto(total: $page->total, results: $this->foundProdsReader->read($page->ids));
    }
}
