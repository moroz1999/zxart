<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Stats\Rest\StatsTopUserRestDto;

#[Map(target: StatsTopUserRestDto::class)]
readonly class StatsTopUserDto
{
    /**
     * @param string[] $badges
     */
    public function __construct(
        public string $name,
        public ?string $url,
        public array $badges,
        public int $count,
    ) {
    }
}
