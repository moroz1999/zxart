<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Groups;

/**
 * @psalm-api
 */
final readonly class RemoveGroupMemberRequestDto
{
    /**
     * @param int $groupId the group
     * @param int $authorId the credited author or author alias
     */
    public function __construct(
        public int $groupId,
        public int $authorId,
    ) {
    }
}
