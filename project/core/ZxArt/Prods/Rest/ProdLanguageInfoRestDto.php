<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdLanguageInfoRestDto
{
    public function __construct(
        public string $code,
        public string $title,
        public string $emoji,
        public string $catalogueUrl,
    ) {
    }
}
