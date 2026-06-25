<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Stats\Rest\StatsDistributionsRestDto;

#[Map(target: StatsDistributionsRestDto::class)]
readonly class StatsDistributionsDto
{
    /**
     * @param int[] $years
     * @param StatsDistributionDto[] $distributions
     */
    public function __construct(
        public array $years,
        #[Map(transform: new MapCollection())]
        public array $distributions,
    ) {
    }
}
