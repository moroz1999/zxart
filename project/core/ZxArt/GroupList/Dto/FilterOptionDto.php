<?php

declare(strict_types=1);

namespace ZxArt\GroupList\Dto;

readonly class FilterOptionDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
