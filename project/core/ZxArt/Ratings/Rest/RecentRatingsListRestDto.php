<?php
declare(strict_types=1);

namespace ZxArt\Ratings\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class RecentRatingsListRestDto
{
    /**
     * @param RecentRatingRestDto[] $items
     * @param bool $hasMore Whether more items exist after this page
     */
    public function __construct(
        #[Map(transform: MapCollection::class)]
        public array $items,
        public bool $hasMore = false,
    ) {
    }
}
