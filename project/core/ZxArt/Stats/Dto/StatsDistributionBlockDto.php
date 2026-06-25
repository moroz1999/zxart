<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Stats\Rest\StatsDistributionBlockRestDto;

#[Map(target: StatsDistributionBlockRestDto::class)]
readonly class StatsDistributionBlockDto
{
    /**
     * @param int[] $years
     */
    public function __construct(
        public array $years,
        public StatsDistributionDto $distribution,
    ) {
    }
}
