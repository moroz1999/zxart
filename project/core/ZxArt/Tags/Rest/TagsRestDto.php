<?php

declare(strict_types=1);

namespace ZxArt\Tags\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class TagsRestDto
{
    /**
     * @param TagRestDto[] $tags
     * @param TagRestDto[] $suggestedTags
     */
    public function __construct(
        public int $elementId,
        #[Map(transform: MapCollection::class)]
        public array $tags,
        #[Map(transform: MapCollection::class)]
        public array $suggestedTags,
    ) {
    }
}
