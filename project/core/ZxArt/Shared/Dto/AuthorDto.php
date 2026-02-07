<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

readonly class AuthorDto
{
    public function __construct(
        public string $name,
        public string $url,
    ) {
    }
}
