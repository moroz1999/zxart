<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use ZxArt\Authors\Dto\AuthorSearchFilterDto;

/**
 * @psalm-api
 */
final readonly class SearchAuthorsRequestDto
{
    /**
     * @param AuthorSearchFilterDto $filter what the authors must match
     * @param int $limit the most results to answer, up to 100
     * @param int $offset how many results of the order to skip
     */
    public function __construct(
        public AuthorSearchFilterDto $filter,
        public int $limit = 20,
        public int $offset = 0,
    ) {
    }
}
