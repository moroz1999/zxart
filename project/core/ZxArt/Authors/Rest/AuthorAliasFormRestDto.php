<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

final readonly class AuthorAliasFormRestDto
{
    public function __construct(
        public AuthorAliasFormAuthorRestDto $author,
    ) {
    }
}
