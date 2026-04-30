<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdSubmitterRestDto
{
    public function __construct(
        public int $id,
        public string $userName,
        public string $url,
    ) {
    }
}
