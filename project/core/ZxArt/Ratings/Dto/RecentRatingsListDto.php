<?php
declare(strict_types=1);

namespace ZxArt\Ratings\Dto;

readonly class RecentRatingsListDto
{
    /**
     * @param RecentRatingDto[] $items
     */
    public function __construct(
        public array $items,
    ) {
    }
}
