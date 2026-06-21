<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Geo\Rest\GeoCityRestDto;

#[Map(target: GeoCityRestDto::class)]
readonly class GeoCityDto
{
    public function __construct(
        public int $id,
        public int $countryId,
        public string $title,
        public string $url,
        public float $latitude,
        public float $longitude,
        public GeoCountersDto $counters,
    ) {
    }
}
