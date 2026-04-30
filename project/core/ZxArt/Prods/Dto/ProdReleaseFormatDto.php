<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

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
