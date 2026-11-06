<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

final readonly class UserGroupItemDto
{
    public function __construct(
        public int $id,
        public string $groupName,
        public string $description,
        public int $membersCount,
    ) {
    }
}
