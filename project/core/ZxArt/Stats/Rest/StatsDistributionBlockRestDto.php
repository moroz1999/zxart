<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

readonly class StatsDistributionBlockRestDto
{
    /**
     * @param int[] $years
     */
    public function __construct(
        public array $years,
        public StatsDistributionRestDto $distribution,
    ) {
    }
}
