<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use ZxArt\Prods\Dto\ProdSearchFilterDto;

/**
 * @psalm-api
 */
final readonly class SearchProdsRequestDto
{
    /**
     * @param ProdSearchFilterDto $filter what the productions must match
     * @param int $limit the most results to answer, up to 100
     * @param int $offset how many results of the order to skip
     */
    public function __construct(
        public ProdSearchFilterDto $filter,
        public int $limit = 20,
        public int $offset = 0,
    ) {
    }
}
