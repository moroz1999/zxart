<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Prods\Rest\ProdSplitDataRestDto;

#[Map(target: ProdSplitDataRestDto::class)]
readonly class ProdSplitDataDto
{
    /**
     * @param ProdSplitGroupDto[] $groups
     */
    public function __construct(
        public int $id,
        public string $title,
        #[Map(transform: MapCollection::class)]
        public array $groups,
    ) {
    }
}
