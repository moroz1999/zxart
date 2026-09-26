<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Releases\Rest\ReleaseDataResultRestDto;

/**
 * Outcome of a release change: the id of the element the change produced.
 *
 * @psalm-api
 */
#[Map(target: ReleaseDataResultRestDto::class)]
final readonly class ReleaseDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
