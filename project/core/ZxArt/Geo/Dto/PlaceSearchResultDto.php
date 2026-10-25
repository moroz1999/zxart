<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

/**
 * @psalm-api
 */
final readonly class PlaceSearchResultDto
{
    /**
     * @param list<FoundCountryDto> $countries
     * @param list<FoundCityDto> $cities
     */
    public function __construct(
        public array $countries,
        public array $cities,
    ) {
    }
}
