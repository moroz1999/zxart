<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

readonly class StatsDistributionRestDto
{
    /**
     * @param string[] $classes
     * @param int[][] $rows
     */
    public function __construct(
        public string $titleKey,
        public array $classes,
        public array $rows,
    ) {
    }
}
