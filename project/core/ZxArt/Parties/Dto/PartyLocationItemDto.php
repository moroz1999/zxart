<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyLocationItemRestDto;

#[Map(target: PartyLocationItemRestDto::class)]
readonly class PartyLocationItemDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
