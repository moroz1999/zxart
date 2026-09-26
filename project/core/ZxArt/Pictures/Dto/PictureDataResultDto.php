<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureDataResultRestDto;

/**
 * Outcome of a picture change: the id of the element the change produced.
 *
 * @psalm-api
 */
#[Map(target: PictureDataResultRestDto::class)]
final readonly class PictureDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
