<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyCountersRestDto;

#[Map(target: PartyCountersRestDto::class)]
readonly class PartyCountersDto
{
    public function __construct(
        public int $compos,
        public int $entries,
        public int $authors,
        public int $pictures,
        public int $tunes,
        public int $prods,
        public int $comments,
    ) {
    }
}
