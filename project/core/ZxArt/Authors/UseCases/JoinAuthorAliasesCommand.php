<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

final readonly class JoinAuthorAliasesCommand
{
    public function __construct(
        public int $aliasId,
        public int $joinedId,
    ) {
    }
}
