<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupDataResultRestDto;

/**
 * Outcome of a group change: the id of the element the change produced.
 */
#[Map(target: GroupDataResultRestDto::class)]
final readonly class GroupDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
