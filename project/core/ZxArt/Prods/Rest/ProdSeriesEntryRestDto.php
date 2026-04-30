<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class ProdSeriesEntryRestDto
{
    /**
     * @param ProdSummaryRestDto[] $prods
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        #[Map(transform: MapCollection::class)]
        public array $prods,
    ) {
    }
}
