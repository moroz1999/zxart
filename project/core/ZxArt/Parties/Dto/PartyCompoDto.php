<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyCompoRestDto;

/**
 * Metadata for a single competition (compo) within a party. The raw {@see self::$compoType}
 * key matches the `compo` field of the linked pictures/tunes (or the prods grouping key), so the
 * Angular page can group works without re-deriving compo names.
 *
 * A compo is only identified by its medium and that key together. The keys are per medium but
 * not distinct across them: a party's general graphics compo and its general music compo are both
 * `standard`, and works entered in no compo are `none` in every medium. {@see self::$slug} carries
 * both and is what the page tab is addressed by.
 */
#[Map(target: PartyCompoRestDto::class)]
readonly class PartyCompoDto
{
    public function __construct(
        public string $compoType,
        public string $medium,
        public string $name,
        public int $count,
        public string $slug,
    ) {
    }
}
