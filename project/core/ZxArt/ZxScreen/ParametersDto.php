<?php
declare(strict_types=1);

namespace ZxArt\ZxScreen;

readonly class ParametersDto
{
    public function __construct(
        public string  $baseURL,
        public ?string $type = null,
        public ?int    $zoom = null,
        public ?string $id = null,
        public ?string $fileName = null,
        public ?bool   $download = null,
        public ?bool   $full = null,
        public ?string $border = null,
        public ?int    $rotation = null,
        public ?string $mode = null,
        public ?string $palette = null,
        public ?bool   $hidden = null
    ) {}
}