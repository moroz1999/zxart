<?php

declare(strict_types=1);

namespace ZxArt\Tags\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\PageMetadata\PageMetadataDto;
use ZxArt\Tags\Rest\TagPageRestDto;

#[Map(target: TagPageRestDto::class)]
final readonly class TagPageDto
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
