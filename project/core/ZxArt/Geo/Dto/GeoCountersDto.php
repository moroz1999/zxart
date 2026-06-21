<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Geo\Rest\GeoCountersRestDto;

#[Map(target: GeoCountersRestDto::class)]
readonly class GeoCountersDto
{
    public function __construct(
        public int $authors,
        public int $groups,
        public int $parties,
    ) {
    }
}
