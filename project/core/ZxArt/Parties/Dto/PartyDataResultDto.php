<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyDataResultRestDto;

/**
 * Outcome of a party change: the id of the element the change produced.
 */
#[Map(target: PartyDataResultRestDto::class)]
final readonly class PartyDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
