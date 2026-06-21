<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Geo\Rest\GeoMapRestDto;

#[Map(target: GeoMapRestDto::class)]
readonly class GeoMapDto
{
    /**
     * @param GeoCountryDto[] $countries
     */
    public function __construct(
        public array $countries,
        public GeoCountersDto $counters,
    ) {
    }
}
