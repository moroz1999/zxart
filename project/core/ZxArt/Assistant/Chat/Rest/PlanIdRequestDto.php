<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Rest;

final readonly class PlanIdRequestDto
{
    public function __construct(
        public int $planId,
    ) {
    }
}
