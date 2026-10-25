<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Search;

use ZxArt\Releases\Dto\ReleaseSearchFilterDto;

/**
 * @psalm-api
 */
final readonly class SearchReleasesRequestDto
{
    /**
     * @param ReleaseSearchFilterDto $filter what the releases must match
     * @param int $limit the most results to answer, up to 100
     * @param int $offset how many results of the order to skip
     */
    public function __construct(
        public ReleaseSearchFilterDto $filter,
        public int $limit = 20,
        public int $offset = 0,
    ) {
    }
}
