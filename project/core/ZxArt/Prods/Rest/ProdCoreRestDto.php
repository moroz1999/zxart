<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdCoreRestDto
{
    public function __construct(
        public int $elementId,
        public string $title,
        public string $prodUrl,
    ) {
    }
}
