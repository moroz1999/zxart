<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Stats\Rest\StatsTopUserRestDto;

#[Map(target: StatsTopUserRestDto::class)]
readonly class StatsTopUserDto
{
    public function __construct(
        public string $name,
        public ?string $url,
        public ?string $badge,
        public int $count,
    ) {
    }
}
