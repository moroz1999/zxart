<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

use ZxArt\Shared\SortDirection;
use ZxArt\Users\UsersListSort;
use ZxArt\Users\UserStatusFilter;

/**
 * What the management users list asks for: a phrase matched against the name
 * and the email, a group (0 for any), a status, the order and the page.
 */
final readonly class UsersListQueryDto
{
    public function __construct(
        public string $search = '',
        public int $groupId = 0,
        public UserStatusFilter $status = UserStatusFilter::All,
        public UsersListSort $sort = UsersListSort::DateCreated,
        public SortDirection $direction = SortDirection::DESC,
        public int $page = 1,
        public int $pageSize = 50,
    ) {
    }
}
