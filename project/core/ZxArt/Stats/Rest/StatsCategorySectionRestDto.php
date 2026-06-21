<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class StatsCategorySectionRestDto
{
    /**
     * @param StatsDistributionRestDto[] $distributions
     * @param StatsTopUserRestDto[] $top
     */
    public function __construct(
        public int $totalWorks,
        public int $peakYear,
        public int $dailyTotal,
        public string $topUnitKey,
        public StatsYearSeriesRestDto $series,
        #[Map(transform: MapCollection::class)]
        public array $distributions,
        public StatsDailySeriesRestDto $daily,
        #[Map(transform: MapCollection::class)]
        public array $top,
    ) {
    }
}
