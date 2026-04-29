<?php

declare(strict_types=1);

namespace ZxArt\Search\Rest;

readonly class SearchResultsRestDto
{
    /**
     * @param SearchResultSetRestDto[] $sets
     */
    public function __construct(
        public string $phrase,
        public int $page,
        public int $pageSize,
        public int $total,
        public array $sets,
    ) {
    }
}
