<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdPartyInfoDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?int $place,
        public ?string $compoLabel,
    ) {
    }
}
