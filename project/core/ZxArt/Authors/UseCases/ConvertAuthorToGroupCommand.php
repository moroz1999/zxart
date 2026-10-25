<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

final readonly class ConvertAuthorToGroupCommand
{
    public function __construct(
        public int $authorId,
    ) {
    }
}
