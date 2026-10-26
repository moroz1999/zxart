<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Catalogues;

/**
 * @psalm-api
 */
final readonly class FindTagsRequestDto
{
    /**
     * @param string $phrase a part of the tag's name or of a synonym, in any language
     * @param int $limit the most tags to answer, up to 100
     */
    public function __construct(
        public string $phrase,
        public int $limit = 20,
    ) {
    }
}
