<?php
declare(strict_types=1);

namespace ZxArt\Ratings\Dto;

readonly class RecentRatingsListDto
{
    /**
     * @param RecentRatingDto[] $items
     * @param bool $hasMore Whether more items exist after this page
     */
    public function __construct(
        public array $items,
        public bool $hasMore = false,
    ) {
    }
}
