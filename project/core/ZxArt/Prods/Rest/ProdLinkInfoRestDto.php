<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdLinkInfoRestDto
{
    public function __construct(
        public string $url,
        public string $name,
        public string $image,
    ) {
    }
}
