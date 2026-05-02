<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdPartyInfoRestDto;

#[Map(target: ProdPartyInfoRestDto::class)]
readonly class ProdPartyInfoDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $abbreviation,
        public string $url,
        public ?int $place,
        public ?string $compoLabel,
    ) {
    }
}
