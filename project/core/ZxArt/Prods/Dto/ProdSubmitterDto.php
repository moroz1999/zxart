<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdSubmitterDto
{
    public function __construct(
        public int $id,
        public string $userName,
        public string $url,
    ) {
    }
}
