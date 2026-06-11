<?php

declare(strict_types=1);

namespace ZxArt\PictureSearch\Rest;

readonly class LocationRestDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
