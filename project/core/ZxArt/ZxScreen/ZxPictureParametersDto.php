<?php
declare(strict_types=1);

namespace ZxArt\ZxScreen;

readonly class ZxPictureParametersDto
{
    public function __construct(
        public ?string $type = null,
        public ?int    $zoom = null,
        public ?int    $id = null,
        public ?string $fileName = null,
        public ?int    $border = null,
        public ?int    $rotation = null,
        public ?string $mode = null,
        public ?string $palette = null,
        public ?bool   $hidden = null,
        public ?string $controller = null
    )
    {
    }
}