<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdReleaseFormatRestDto;

#[Map(target: ProdReleaseFormatRestDto::class)]
readonly class ProdReleaseFormatDto
{
    public function __construct(
        public string $format,
        public string $label,
        public string $emoji,
        public string $catalogueUrl,
    ) {
    }
}
