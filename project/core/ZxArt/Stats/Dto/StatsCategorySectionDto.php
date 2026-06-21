<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Stats\Rest\StatsCategorySectionRestDto;

#[Map(target: StatsCategorySectionRestDto::class)]
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
        #[Map(transform: new MapCollection())]
        public array $distributions,
        public StatsDailySeriesDto $daily,
        #[Map(transform: new MapCollection())]
        public array $top,
    ) {
    }
}
