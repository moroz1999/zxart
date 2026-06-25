<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

readonly class StatsTopUserRestDto
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
