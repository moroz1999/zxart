<?php

declare(strict_types=1);

namespace ZxArt\Geo\Rest;

readonly class GeoMapRestDto
{
    /**
     * @param GeoCountryRestDto[] $countries
     */
    public function __construct(
        public array $countries,
        public GeoCountersRestDto $counters,
    ) {
    }
}
