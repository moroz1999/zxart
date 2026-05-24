<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorGroupRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $years,
    ) {
    }
}
