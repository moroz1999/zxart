<?php

declare(strict_types=1);

namespace ZxArt\Tags\Dto;

readonly class TagsDto
{
    /**
     * @param TagDto[] $tags
     * @param TagDto[] $suggestedTags
     */
    public function __construct(
        public int $elementId,
        public array $tags,
        public array $suggestedTags,
    ) {
    }
}
