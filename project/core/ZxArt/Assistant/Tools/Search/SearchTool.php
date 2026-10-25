<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Search\Services\EntitySearchService;
use ZxArt\Shared\EntityType;

/**
 * @implements ReadTool<SearchRequestDto>
 *
 * @psalm-api
 */
final readonly class SearchTool implements ReadTool
{
    public function __construct(
        private EntitySearchService $entitySearchService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'search';
    }

    #[Override]
    public function description(): string
    {
        return 'Finds productions, releases, authors, author aliases, groups and group aliases by a phrase in '
            . 'their titles (and an author\'s real name). The closest titles come first, of any type. Each result has '
            . 'its type, id, title, year and a qualifier telling it apart: an author\'s real name and country, a '
            . 'production\'s groups, a release\'s production, an alias\'s author or group, a group\'s country.';
    }

    #[Override]
    public function requestClass(): string
    {
        return SearchRequestDto::class;
    }

    #[Override]
    public function execute(object $request): FoundEntitiesDto
    {
        $types = array_map(static fn(SearchableType $type): EntityType => $type->entityType(), $request->types ?? []);

        return new FoundEntitiesDto($this->entitySearchService->search($request->phrase, $types, $request->limit));
    }
}
