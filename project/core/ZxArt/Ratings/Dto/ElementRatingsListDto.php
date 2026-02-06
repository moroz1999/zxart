<?php
declare(strict_types=1);

namespace ZxArt\Ratings\Dto;

readonly class ElementRatingsListDto
{
    /**
     * @param ElementRatingDto[] $items
     */
    public function __construct(
        public array $items,
    ) {
    }
}
