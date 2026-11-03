<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Groups\Services\FoundGroupsReader;
use ZxArt\Groups\Services\GroupSearchService;

/**
 * Finds groups by typed filters, one page at a time.
 *
 * @psalm-api
 */
final readonly class SearchGroupsAction implements ReadAction
{
    public function __construct(
        private GroupSearchService $groupSearchService,
        private FoundGroupsReader $foundGroupsReader,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'search_groups';
    }

    #[Override]
    public function description(): string
    {
        return 'Finds groups by typed filters, every filter given must hold: a part of the title, abbreviation or an '
            . 'alias, countries, cities, types, founding and ending dates (Unix timestamps), members, active years '
            . '(the years of the software they made or published), productions given by id or found by a nested '
            . 'production filter and whether the group made or published them, the subgroups of a group and the '
            . 'groups a group belongs to. What an alias did counts for its group. Answers one page by title and the '
            . 'total; each group with abbreviation, type, country, city and aliases.';
    }

    #[Override]
    public function schema(): string
    {
        return SearchGroupsArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): FoundGroupsDto
    {
        $search = SearchGroupsArgumentsDto::from($arguments);
        $page = $this->groupSearchService->search($search->filter, $search->page->offset, $search->page->limit);

        return new FoundGroupsDto(total: $page->total, results: $this->foundGroupsReader->read($page->ids));
    }
}
