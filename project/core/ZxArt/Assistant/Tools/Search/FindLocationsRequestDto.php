<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

/**
 * @psalm-api
 */
final readonly class FindLocationsRequestDto
{
    /**
     * @param string $phrase a part of a country's or a city's name, in any language
     */
    public function __construct(
        public string $phrase,
    ) {
    }
}
