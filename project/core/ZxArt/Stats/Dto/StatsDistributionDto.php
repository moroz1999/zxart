<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

readonly class StatsDistributionDto
{
    /**
     * @param string[] $classes Class labels (column values), in display order.
     * @param int[][] $rows One row per year; each row holds the count per class in the same order as $classes.
     */
    public function __construct(
        public string $titleKey,
        public array $classes,
        public array $rows,
    ) {
    }
}
