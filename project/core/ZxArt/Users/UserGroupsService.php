<?php

declare(strict_types=1);

namespace ZxArt\Users;

use ZxArt\Users\Dbal\UserGroupsDbal;
use ZxArt\Users\Dto\UserGroupItemDto;

/**
 * Every user group with how many users belong to it, by name.
 *
 * @psalm-api
 */
final readonly class UserGroupsService
{
    public function __construct(
        private UserGroupsDbal $userGroupsDbal,
    ) {
    }

    /**
     * @return list<UserGroupItemDto>
     */
    public function getGroups(): array
    {
        $counts = $this->userGroupsDbal->getMembersCounts();

        return array_map(static fn(array $row): UserGroupItemDto => new UserGroupItemDto(
            id: $row['id'],
            groupName: $row['groupName'],
            description: $row['description'],
            membersCount: $counts[$row['id']] ?? 0,
        ), $this->userGroupsDbal->getGroups());
    }
}
