<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Geo\Dto\PlaceSearchResultDto;
use ZxArt\Geo\PlaceSearchService;

/**
 * @implements ReadTool<FindLocationsRequestDto>
 *
 * @psalm-api
 */
final readonly class FindLocationsTool implements ReadTool
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
    public function requestClass(): string
    {
        return FindLocationsRequestDto::class;
    }

    #[Override]
    public function execute(object $request): PlaceSearchResultDto
    {
        return $this->placeSearchService->find($request->phrase);
    }
}
