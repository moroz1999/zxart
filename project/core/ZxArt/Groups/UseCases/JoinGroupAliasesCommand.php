<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

final readonly class JoinGroupAliasesCommand
{
    public function __construct(
        public int $aliasId,
        public int $joinedId,
    ) {
    }
}
