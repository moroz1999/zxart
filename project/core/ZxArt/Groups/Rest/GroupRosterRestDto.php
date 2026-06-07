<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class GroupRosterRestDto
{
    /**
     * @param GroupSubgroupRestDto[] $subgroups
     * @param GroupMemberRestDto[]   $members
     */
    public function __construct(
        #[Map(transform: MapCollection::class)]
        public array $subgroups,
        #[Map(transform: MapCollection::class)]
        public array $members,
    ) {
    }
}
