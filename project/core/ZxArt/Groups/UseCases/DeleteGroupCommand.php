<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

final readonly class DeleteGroupCommand
{
    public function __construct(
        public int $groupId,
    ) {
    }
}
