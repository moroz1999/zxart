<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupTabsRestDto;

#[Map(target: GroupTabsRestDto::class)]
readonly class GroupTabsDto
{
    public function __construct(
        public bool $hasProds,
        public bool $hasPublished,
        public bool $hasReleases,
        public bool $hasMembers,
        public bool $hasSubgroups,
        public bool $hasConnections,
        public bool $hasMentions,
    ) {
    }
}
