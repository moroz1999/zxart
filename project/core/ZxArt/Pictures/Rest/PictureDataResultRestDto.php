<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

/**
 * @psalm-api
 */
final readonly class PictureDataResultRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
