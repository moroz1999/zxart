<?php

declare(strict_types=1);

namespace ZxArt\TagsList\Dto;

readonly class TagListItemDto
{
    public function __construct(
        public int $id,
        public string $title,
        public int $amount,
    ) {
    }
}
