<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Stats\Rest\StatsOverviewRestDto;

#[Map(target: StatsOverviewRestDto::class)]
readonly class StatsOverviewDto
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
