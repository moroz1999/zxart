<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorLocationItemRestDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
