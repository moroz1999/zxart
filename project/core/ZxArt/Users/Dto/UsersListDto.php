<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

final readonly class UsersListDto
{
    /**
     * @param list<UserListItemDto> $users
     */
    public function __construct(
        public int $total,
        public array $users,
    ) {
    }
}
