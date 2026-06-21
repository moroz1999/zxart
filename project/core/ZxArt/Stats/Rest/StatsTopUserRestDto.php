<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

readonly class StatsTopUserRestDto
{
    public function __construct(
        public string $name,
        public ?string $url,
        public ?string $badge,
        public int $count,
    ) {
    }
}
