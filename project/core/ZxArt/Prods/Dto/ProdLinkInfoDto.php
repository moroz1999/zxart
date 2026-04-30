<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdLinkInfoDto
{
    public function __construct(
        public string $url,
        public string $name,
        public string $image,
    ) {
    }
}
