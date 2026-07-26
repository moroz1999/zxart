<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyRestDto;

#[Map(target: PartyRestDto::class)]
readonly class PartyDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $year,
        public string $imageUrl,
        public ?PartyLocationItemDto $country = null,
        public ?PartyLocationItemDto $city = null,
    ) {
    }
}
