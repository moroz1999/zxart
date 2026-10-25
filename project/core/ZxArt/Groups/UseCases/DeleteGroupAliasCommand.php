<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

final readonly class DeleteGroupAliasCommand
{
    public function __construct(
        public int $aliasId,
    ) {
    }
}
