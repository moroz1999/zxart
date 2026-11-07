<?php

declare(strict_types=1);

namespace ZxArt\Tags\Dto;

/**
 * A tag a tag picker offers for a phrase, in the current language.
 */
final readonly class TagLookupItemDto
{
    public function __construct(
        public int $id,
        /** the name, its synonyms and its description, for the list of suggestions */
        public string $title,
        /** the tag's own name, the value forms save and searches filter by */
        public string $value,
        public string $synonym,
        public string $description,
        public string $url,
    ) {
    }
}
