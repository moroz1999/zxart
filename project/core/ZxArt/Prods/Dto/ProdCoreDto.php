<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdCoreDto
{
    public function __construct(
        public int $elementId,
        public string $title,
        public string $prodUrl,
    ) {
    }
}
