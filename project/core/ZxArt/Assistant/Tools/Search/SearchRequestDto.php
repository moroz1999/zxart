<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

/**
 * @psalm-api
 */
final readonly class SearchRequestDto
{
    /**
     * @param string $phrase a part of the title, or of an author's real name
     * @param ?SearchableType[] $types the types to search; all six when not given
     * @param int $limit the most results to answer, up to 100
     */
    public function __construct(
        public string $phrase,
        public ?array $types = null,
        public int $limit = 20,
    ) {
    }
}
