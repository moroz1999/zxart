<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdHardwareInfoRestDto
{
    public function __construct(
        public string $id,
        public string $title,
    ) {
    }
}
