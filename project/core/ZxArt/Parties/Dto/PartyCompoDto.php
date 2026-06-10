<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyCompoRestDto;

/**
 * Metadata for a single competition (compo) within a party. The raw {@see self::$compoType}
 * key matches the `compo` field of the linked pictures/tunes (or the prods grouping key), so the
 * Angular page can group works without re-deriving compo names.
 */
#[Map(target: PartyCompoRestDto::class)]
readonly class PartyCompoDto
{
    public function __construct(
        public string $compoType,
        public string $medium,
        public string $name,
        public int $count,
    ) {
    }
}
