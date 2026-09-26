<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupAliasDataResultRestDto;

/**
 * Outcome of a group alias change: the id of the element the change produced.
 *
 * @psalm-api
 */
#[Map(target: GroupAliasDataResultRestDto::class)]
final readonly class GroupAliasDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
