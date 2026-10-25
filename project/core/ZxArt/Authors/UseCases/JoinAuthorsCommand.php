<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

final readonly class JoinAuthorsCommand
{
    public function __construct(
        public int $authorId,
        public int $joinedId,
        public bool $asAlias = false,
    ) {
    }
}
