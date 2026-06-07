<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

readonly class GroupTabsRestDto
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
