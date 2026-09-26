<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Tunes\Rest\TuneDataResultRestDto;

/**
 * Outcome of a tune change: the id of the element the change produced.
 *
 * @psalm-api
 */
#[Map(target: TuneDataResultRestDto::class)]
final readonly class TuneDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
