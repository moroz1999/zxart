<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

final readonly class JoinGroupsCommand
{
    public function __construct(
        public int $groupId,
        public int $joinedId,
        public bool $asAlias = false,
    ) {
    }
}
