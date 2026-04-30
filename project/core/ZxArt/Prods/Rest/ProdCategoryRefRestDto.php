<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdCategoryRefRestDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
