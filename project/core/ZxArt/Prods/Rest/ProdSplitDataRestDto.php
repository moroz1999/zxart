<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class ProdSplitDataRestDto
{
    /**
     * @param ProdSplitGroupRestDto[] $groups
     */
    public function __construct(
        public int $id,
        public string $title,
        #[Map(transform: MapCollection::class)]
        public array $groups,
    ) {
    }
}
