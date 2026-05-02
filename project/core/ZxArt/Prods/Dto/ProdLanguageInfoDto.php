<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdLanguageInfoRestDto;

#[Map(target: ProdLanguageInfoRestDto::class)]
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
