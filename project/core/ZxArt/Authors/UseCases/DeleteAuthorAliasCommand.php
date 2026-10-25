<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

final readonly class DeleteAuthorAliasCommand
{
    public function __construct(
        public int $aliasId,
    ) {
    }
}
