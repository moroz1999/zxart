<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdLanguageInfoDto
{
    public function __construct(
        public string $code,
        public string $title,
        public string $emoji,
        public string $catalogueUrl,
    ) {
    }
}
