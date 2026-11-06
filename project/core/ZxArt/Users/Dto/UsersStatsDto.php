<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

final readonly class UsersStatsDto
{
    /**
     * @param list<MonthlyCountDto> $registrationsByMonth oldest month first
     */
    public function __construct(
        public int $total,
        public int $newLastWeek,
        public int $newLastMonth,
        public int $banned,
        public int $unverified,
        public array $registrationsByMonth,
    ) {
    }
}
