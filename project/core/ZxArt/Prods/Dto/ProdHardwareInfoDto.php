<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdHardwareInfoRestDto;

#[Map(target: ProdHardwareInfoRestDto::class)]
readonly class ProdHardwareInfoDto
{
    public function __construct(
        public string $id,
        public string $title,
    ) {
    }
}
