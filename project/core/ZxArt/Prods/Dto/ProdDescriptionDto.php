<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdDescriptionRestDto;

#[Map(target: ProdDescriptionRestDto::class)]
readonly class ProdDescriptionDto
{
    public function __construct(
        public string $description,
        public bool $htmlDescription,
        public string $instructions,
    ) {
    }
}
