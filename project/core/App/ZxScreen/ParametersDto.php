<?php

namespace App\ZxScreen;

class ParametersDto
{
    public function __construct(
        public readonly string $baseURL,
        public readonly ?string $type = null,
        public readonly ?int $zoom = null,
        public readonly ?string $id = null,
        public readonly ?string $fileName = null,
        public readonly ?bool $download = null,
        public readonly ?bool $full = null,
        public readonly ?string $border = null,
        public readonly ?int $rotation = null,
        public readonly ?string $mode = null,
        public readonly ?string $palette = null,
        public readonly ?bool $hidden = null
    ) {}
}