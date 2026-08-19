<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdSplitItemRestDto;

#[Map(target: ProdSplitItemRestDto::class)]
readonly class ProdSplitItemDto
{
    public function __construct(
        public string $key,
        public string $title,
        public ?string $url,
        public ?string $imageUrl,
    ) {
    }
}
