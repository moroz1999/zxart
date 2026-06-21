<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

readonly class StatsCategorySectionDto
{
    /**
     * @param StatsDistributionDto[] $distributions
     * @param StatsTopUserDto[] $top
     */
    public function __construct(
        public int $totalWorks,
        public int $peakYear,
        public int $dailyTotal,
        public string $topUnitKey,
        public StatsYearSeriesDto $series,
        public array $distributions,
        public StatsDailySeriesDto $daily,
        public array $top,
    ) {
    }
}
