<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

final readonly class DeleteGroupMemberCommand
{
    public function __construct(
        public int $groupId,
        public int $authorId,
    ) {
    }
}
