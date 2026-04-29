<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

readonly class SearchResultsDto
{
    /**
     * @param SearchResultSetDto[] $sets
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
