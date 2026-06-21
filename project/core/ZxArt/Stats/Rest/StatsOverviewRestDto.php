<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

readonly class StatsOverviewRestDto
{
    public function __construct(
        public int $prods,
        public int $releases,
        public int $authors,
        public int $authorsWithAliases,
        public int $groups,
        public int $groupsWithAliases,
        public int $music,
        public int $pictures,
    ) {
    }
}
