<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdGroupRefDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
