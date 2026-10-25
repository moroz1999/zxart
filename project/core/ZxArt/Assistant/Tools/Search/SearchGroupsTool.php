<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Groups\Services\FoundGroupsReader;
use ZxArt\Groups\Services\GroupSearchService;

/**
 * @implements ReadTool<SearchGroupsRequestDto>
 *
 * @psalm-api
 */
final readonly class SearchGroupsTool implements ReadTool
{
    private const int MAX_LIMIT = 100;

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
    public function requestClass(): string
    {
        return SearchGroupsRequestDto::class;
    }

    #[Override]
    public function execute(object $request): FoundGroupsDto
    {
        $limit = min(max(1, $request->limit), self::MAX_LIMIT);
        $page = $this->groupSearchService->search($request->filter, max(0, $request->offset), $limit);

        return new FoundGroupsDto(total: $page->total, results: $this->foundGroupsReader->read($page->ids));
    }
}
