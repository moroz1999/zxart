<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

readonly class SearchResultPreviewDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public string $structureType,
    ) {
    }
}
