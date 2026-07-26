<?php

declare(strict_types=1);

namespace ZxArt\Tags\Rest;

use ZxArt\PageMetadata\PageMetadataDto;

final readonly class TagPageRestDto
{
    public function __construct(
        public int $id,
        public string $section,
        public string $title,
        public string $heading,
        public PageMetadataDto $metadata,
    ) {
    }
}
