<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdReleaseFormatRestDto
{
    public function __construct(
        public string $format,
        public string $label,
        public string $emoji,
        public string $catalogueUrl,
    ) {
    }
}
