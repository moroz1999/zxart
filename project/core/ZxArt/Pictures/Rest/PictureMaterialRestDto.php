<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

readonly class PictureMaterialRestDto
{
    public function __construct(
        public string $id,
        public string $kind,
        public string $label,
        public string $imageUrl,
    ) {
    }
}
