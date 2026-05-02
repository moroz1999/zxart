<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdSubmitterRestDto;

#[Map(target: ProdSubmitterRestDto::class)]
readonly class ProdSubmitterDto
{
    public function __construct(
        public int $id,
        public string $userName,
        public string $url,
    ) {
    }
}
