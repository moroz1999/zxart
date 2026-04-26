<?php

declare(strict_types=1);

namespace ZxArt\Search\Rest;

readonly class SearchResultSetRestDto
{
    /**
     * @param SearchItemRestDto[] $items
     */
    public function __construct(
        public string $type,
        public bool $partial,
        public int $totalCount,
        public array $items,
    ) {
    }
}
