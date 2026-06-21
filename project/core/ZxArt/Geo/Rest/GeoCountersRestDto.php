<?php

declare(strict_types=1);

namespace ZxArt\Geo\Rest;

readonly class GeoCountersRestDto
{
    public function __construct(
        public int $authors,
        public int $groups,
        public int $parties,
    ) {
    }
}
