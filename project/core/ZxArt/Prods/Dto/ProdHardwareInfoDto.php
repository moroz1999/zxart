<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdHardwareInfoDto
{
    public function __construct(
        public string $id,
        public string $title,
    ) {
    }
}
