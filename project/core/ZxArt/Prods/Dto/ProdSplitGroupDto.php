<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Prods\Rest\ProdSplitGroupRestDto;

#[Map(target: ProdSplitGroupRestDto::class)]
readonly class ProdSplitGroupDto
{
    /**
     * @param ProdSplitItemDto[] $items
     */
    public function __construct(
        public string $group,
        #[Map(transform: MapCollection::class)]
        public array $items,
    ) {
    }
}
