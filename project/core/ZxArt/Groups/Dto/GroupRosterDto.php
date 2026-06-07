<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupRosterRestDto;

#[Map(target: GroupRosterRestDto::class)]
readonly class GroupRosterDto
{
    /**
     * @param GroupSubgroupDto[] $subgroups
     * @param GroupMemberDto[]   $members
     */
    public function __construct(
        public array $subgroups,
        public array $members,
    ) {
    }
}
