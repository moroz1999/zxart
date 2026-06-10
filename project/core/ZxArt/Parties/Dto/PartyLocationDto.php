<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyLocationRestDto;

#[Map(target: PartyLocationRestDto::class)]
readonly class PartyLocationDto
{
    public function __construct(
        public ?PartyLocationItemDto $city,
        public ?PartyLocationItemDto $country,
    ) {
    }
}
