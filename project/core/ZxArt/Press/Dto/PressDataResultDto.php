<?php

declare(strict_types=1);

namespace ZxArt\Press\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Press\Rest\PressDataResultRestDto;

/**
 * Outcome of a press article change: the id of the element the change produced.
 */
#[Map(target: PressDataResultRestDto::class)]
final readonly class PressDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
