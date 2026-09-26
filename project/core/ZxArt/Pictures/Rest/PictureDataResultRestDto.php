<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

final readonly class PictureDataResultRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
