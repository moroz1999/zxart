<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorAliasDataResultRestDto;

/**
 * Outcome of a author alias change: the id of the element the change produced.
 */
#[Map(target: AuthorAliasDataResultRestDto::class)]
final readonly class AuthorAliasDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
