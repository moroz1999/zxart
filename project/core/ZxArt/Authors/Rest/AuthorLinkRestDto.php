<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorLinkRestDto
{
    public function __construct(
        public string $url,
        public string $label,
    ) {
    }
}
