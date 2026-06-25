<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Stats\Rest\StatsCategorySummaryRestDto;

#[Map(target: StatsCategorySummaryRestDto::class)]
readonly class StatsCategorySummaryDto
{
    public function __construct(
        public int $totalWorks,
        public int $peakYear,
        public int $dailyTotal,
    ) {
    }
}
