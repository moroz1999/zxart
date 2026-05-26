<?php
declare(strict_types=1);

namespace ZxArt\Ratings\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class AuthorRatingsListRestDto
{
    /**
     * @param RecentRatingRestDto[] $items
     */
    public function __construct(
        #[Map(transform: MapCollection::class)]
        public array $items,
        public int $currentPage,
        public int $pagesAmount,
        public int $totalCount,
    ) {
    }
}
