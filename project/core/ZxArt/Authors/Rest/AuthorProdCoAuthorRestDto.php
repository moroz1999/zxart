<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorProdCoAuthorRestDto
{
    public function __construct(
        public string $name,
        public string $url,
    ) {
    }
}
