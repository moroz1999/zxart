<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

final readonly class AuthorAliasFormAuthorRestDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
