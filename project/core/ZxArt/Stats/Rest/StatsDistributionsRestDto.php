<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class StatsDistributionsRestDto
{
    /**
     * @param int[] $years
     * @param StatsDistributionRestDto[] $distributions
     */
    public function __construct(
        public array $years,
        #[Map(transform: new MapCollection())]
        public array $distributions,
    ) {
    }
}
