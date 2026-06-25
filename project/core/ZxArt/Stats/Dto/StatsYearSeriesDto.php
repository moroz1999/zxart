<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Stats\Rest\StatsYearSeriesRestDto;

#[Map(target: StatsYearSeriesRestDto::class)]
readonly class StatsYearSeriesDto
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
