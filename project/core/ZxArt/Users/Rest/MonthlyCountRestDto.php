<?php

declare(strict_types=1);

namespace ZxArt\Users\Rest;

readonly class MonthlyCountRestDto
{
    public function __construct(
        public string $month,
        public int $count,
    ) {
    }
}
