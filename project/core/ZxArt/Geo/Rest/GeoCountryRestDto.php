<?php

declare(strict_types=1);

namespace ZxArt\Geo\Rest;

readonly class GeoCountryRestDto
{
    /**
     * @param GeoCityRestDto[] $cities
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public float $latitude,
        public float $longitude,
        public GeoCountersRestDto $counters,
        public array $cities,
    ) {
    }
}
