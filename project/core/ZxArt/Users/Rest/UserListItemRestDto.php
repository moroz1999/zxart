<?php

declare(strict_types=1);

namespace ZxArt\Users\Rest;

readonly class UserListItemRestDto
{
    /**
     * @param list<int> $groupIds
     */
    public function __construct(
        public int $id,
        public string $userName,
        public string $email,
        public int $dateCreated,
        public bool $verified,
        public bool $banned,
        public array $groupIds,
    ) {
    }
}
