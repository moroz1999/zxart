<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyEditionRestDto;

/**
 * Another edition (year) of the same party series, used for the year navigation strip.
 */
#[Map(target: PartyEditionRestDto::class)]
readonly class PartyEditionDto
{
    public function __construct(
        public int $id,
        public string $year,
        public string $url,
        public bool $current,
    ) {
    }
}
