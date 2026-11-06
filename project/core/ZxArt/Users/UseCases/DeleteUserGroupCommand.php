<?php

declare(strict_types=1);

namespace ZxArt\Users\UseCases;

final readonly class DeleteUserGroupCommand
{
    public function __construct(
        public int $groupId,
    ) {
    }
}
