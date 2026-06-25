<?php

declare(strict_types=1);

namespace ZxArt\AuthorList\Dto;

readonly class ActiveAuthorDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
