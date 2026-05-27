<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorBreadcrumbRestDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
