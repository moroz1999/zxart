<?php
declare(strict_types=1);

namespace ZxArt\Ratings\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class ElementRatingsListRestDto
{
    /**
     * @param ElementRatingRestDto[] $items
     */
    public function __construct(
        #[Map(transform: MapCollection::class)]
        public array $items,
    ) {
    }
}
