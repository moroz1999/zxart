<?php

declare(strict_types=1);

namespace ZxArt\Users;

use ZxArt\Users\Dbal\UserGroupsDbal;
use ZxArt\Users\Dbal\UsersListDbal;
use ZxArt\Users\Dto\UserListItemDto;
use ZxArt\Users\Dto\UsersListDto;
use ZxArt\Users\Dto\UsersListQueryDto;

/**
 * One page of the management users list, each user with their groups.
 *
 * @psalm-api
 */
final readonly class UsersListService
{
    public function __construct(
        private UsersListDbal $usersListDbal,
        private UserGroupsDbal $userGroupsDbal,
    ) {
    }

    public function getList(UsersListQueryDto $query): UsersListDto
    {
        $rows = $this->usersListDbal->find($query);
        $groupIds = $this->userGroupsDbal->getGroupIdsOfUsers(array_column($rows, 'id'));

        return new UsersListDto(
            total: $this->usersListDbal->count($query),
            users: array_map(static fn(array $row): UserListItemDto => new UserListItemDto(
                id: $row['id'],
                userName: $row['userName'],
                email: $row['email'],
                dateCreated: $row['dateCreated'],
                verified: $row['verified'],
                banned: $row['banned'],
                groupIds: $groupIds[$row['id']] ?? [],
            ), $rows),
        );
    }
}
