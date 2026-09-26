<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

final readonly class AuthorAliasDataResultRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
