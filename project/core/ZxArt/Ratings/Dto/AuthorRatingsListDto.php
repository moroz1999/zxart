<?php
declare(strict_types=1);

namespace ZxArt\Ratings\Dto;

readonly class AuthorRatingsListDto
{
    /**
     * @param RecentRatingDto[] $items
     */
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $pagesAmount,
        public int $totalCount,
    ) {
    }
}
