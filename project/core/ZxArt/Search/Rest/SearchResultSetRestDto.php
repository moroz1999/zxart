<?php

declare(strict_types=1);

namespace ZxArt\Search\Rest;

readonly class SearchResultSetRestDto
{
    /**
     * @param object[] $items
     */
    public function __construct(
        public string $type,
        public int $totalCount,
        public array $items,
    ) {
    }
}
