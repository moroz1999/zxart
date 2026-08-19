<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class ProdSplitGroupRestDto
{
    /**
     * @param ProdSplitItemRestDto[] $items
     */
    public function __construct(
        public string $group,
        #[Map(transform: MapCollection::class)]
        public array $items,
    ) {
    }
}
