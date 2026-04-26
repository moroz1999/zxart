<?php

declare(strict_types=1);

namespace ZxArt\Search\Rest;

readonly class SearchResultsRestDto
{
    /**
     * @param SearchResultSetRestDto[] $sets
     * @param string[]                 $availableTypes
     */
    public function __construct(
        public string $phrase,
        public int $page,
        public int $pageSize,
        public int $total,
        public bool $exactMatches,
        public array $sets,
        public array $availableTypes,
    ) {
    }
}
