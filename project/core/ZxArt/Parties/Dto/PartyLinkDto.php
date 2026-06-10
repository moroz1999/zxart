<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyLinkRestDto;

#[Map(target: PartyLinkRestDto::class)]
readonly class PartyLinkDto
{
    public function __construct(
        public string $url,
        public string $label,
    ) {
    }
}
