<?php

declare(strict_types=1);

namespace ZxArt\Geo\Rest;

readonly class GeoCityRestDto
{
    public function __construct(
        public int $id,
        public int $countryId,
        public string $title,
        public string $url,
        public float $latitude,
        public float $longitude,
        public GeoCountersRestDto $counters,
    ) {
    }
}
