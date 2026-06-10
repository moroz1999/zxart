<?php

declare(strict_types=1);

namespace ZxArt\Parties\Rest;

readonly class PartyTabsRestDto
{
    public function __construct(
        public bool $hasOverview,
        public bool $hasCompos,
        public bool $hasActivity,
    ) {
    }
}
