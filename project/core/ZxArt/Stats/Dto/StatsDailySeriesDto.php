<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

readonly class StatsDailySeriesDto
{
    /**
     * @param string[] $dates Day labels in d.m.Y format, oldest first.
     * @param int[] $data Daily counts aligned with $dates.
     */
    public function __construct(
        public string $labelKey,
        public array $dates,
        public array $data,
    ) {
    }
}
