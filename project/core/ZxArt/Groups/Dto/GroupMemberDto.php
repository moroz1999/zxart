<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupMemberRestDto;

#[Map(target: GroupMemberRestDto::class)]
readonly class GroupMemberDto
{
    /**
     * @param string[] $roles
     * @param string[] $subgroups
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public string $realName,
        public array $roles,
        public ?string $years,
        public array $subgroups,
    ) {
    }
}
