<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

final readonly class AuthorAliasCreatedRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
