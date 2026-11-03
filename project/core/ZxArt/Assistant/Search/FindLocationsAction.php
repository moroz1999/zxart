<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Search;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Geo\Dto\PlaceSearchResultDto;
use ZxArt\Geo\PlaceSearchService;

/**
 * @psalm-api
 */
final readonly class FindLocationsAction implements ReadAction
{
    public function __construct(
        private PlaceSearchService $placeSearchService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'find_locations';
    }

    #[Override]
    public function description(): string
    {
        return 'Finds countries and cities by a part of their name in any language, with their ids, to filter '
            . 'authors and groups by location. A city comes with its country.';
    }

    #[Override]
    public function schema(): string
    {
        return FindLocationsArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): PlaceSearchResultDto
    {
        $request = FindLocationsArgumentsDto::from($arguments);
        return $this->placeSearchService->find($request->phrase);
    }
}
