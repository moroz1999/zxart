<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class ProdCoverGroupRestDto
{
    /**
     * @param ProdReleaseInlayRestDto[] $items
     */
    public function __construct(
        public string $kind,
        #[Map(transform: MapCollection::class)]
        public array $items,
    ) {
    }
}
