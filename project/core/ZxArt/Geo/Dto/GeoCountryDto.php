<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Geo\Rest\GeoCountryRestDto;

#[Map(target: GeoCountryRestDto::class)]
readonly class GeoCountryDto
{
    /**
     * @param GeoCityDto[] $cities
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public float $latitude,
        public float $longitude,
        public GeoCountersDto $counters,
        public array $cities,
    ) {
    }
}
