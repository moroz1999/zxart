<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Search;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Search\Services\EntitySearchService;
use ZxArt\Shared\EntityType;

/**
 * @psalm-api
 */
final readonly class SearchAction implements ReadAction
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
    public function schema(): string
    {
        return SearchArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): FoundEntitiesDto
    {
        $request = SearchArgumentsDto::from($arguments);
        $types = array_map(static fn(SearchableType $type): EntityType => $type->entityType(), $request->types);

        return new FoundEntitiesDto($this->entitySearchService->search($request->phrase, $types, $request->limit));
    }
}
