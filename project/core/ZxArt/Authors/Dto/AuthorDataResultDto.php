<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorDataResultRestDto;

/**
 * Outcome of a author change: the id of the element the change produced.
 *
 * @psalm-api
 */
#[Map(target: AuthorDataResultRestDto::class)]
final readonly class AuthorDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
