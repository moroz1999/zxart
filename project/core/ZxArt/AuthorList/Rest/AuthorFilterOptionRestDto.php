<?php

declare(strict_types=1);

namespace ZxArt\AuthorList\Rest;

readonly class AuthorFilterOptionRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
