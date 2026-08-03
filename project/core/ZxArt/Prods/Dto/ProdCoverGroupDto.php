<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdCoverGroupRestDto;

#[Map(target: ProdCoverGroupRestDto::class)]
readonly class ProdCoverGroupDto
{
    /**
     * @param ProdReleaseInlayDto[] $items
     */
    public function __construct(
        public string $kind,
        public array $items,
    ) {
    }
}
