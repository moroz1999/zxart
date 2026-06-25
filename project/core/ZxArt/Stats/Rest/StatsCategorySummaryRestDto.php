<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

readonly class StatsCategorySummaryRestDto
{
    public function __construct(
        public int $totalWorks,
        public int $peakYear,
        public int $dailyTotal,
    ) {
    }
}
