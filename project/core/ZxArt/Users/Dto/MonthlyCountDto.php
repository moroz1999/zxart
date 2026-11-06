<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

final readonly class MonthlyCountDto
{
    public function __construct(
        /** Y-m */
        public string $month,
        public int $count,
    ) {
    }
}
