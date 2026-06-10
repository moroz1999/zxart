<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyTabsRestDto;

#[Map(target: PartyTabsRestDto::class)]
readonly class PartyTabsDto
{
    public function __construct(
        public bool $hasOverview,
        public bool $hasCompos,
        public bool $hasActivity,
    ) {
    }
}
