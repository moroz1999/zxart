<?php

declare(strict_types=1);

namespace ZxArt\Tags\Dto;

readonly class TagDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description = null,
    ) {
    }
}
