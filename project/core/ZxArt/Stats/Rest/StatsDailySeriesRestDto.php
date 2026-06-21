<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

readonly class StatsDailySeriesRestDto
{
    /**
     * @param string[] $dates
     * @param int[] $data
     */
    public function __construct(
        public string $labelKey,
        public array $dates,
        public array $data,
    ) {
    }
}
