<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

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
