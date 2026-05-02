<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdLinkInfoRestDto;

#[Map(target: ProdLinkInfoRestDto::class)]
readonly class ProdLinkInfoDto
{
    public function __construct(
        public string $url,
        public string $name,
        public string $image,
    ) {
    }
}
