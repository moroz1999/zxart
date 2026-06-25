<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

readonly class StatsYearSeriesRestDto
{
    /**
     * @param int[] $years
     * @param int[] $all
     * @param int[] $rated
     */
    public function __construct(
        public array $years,
        public array $all,
        public array $rated,
    ) {
    }
}
