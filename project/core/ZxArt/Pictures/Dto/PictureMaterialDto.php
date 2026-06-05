<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureMaterialRestDto;

#[Map(target: PictureMaterialRestDto::class)]
readonly class PictureMaterialDto
{
    public function __construct(
        public string $id,
        public string $kind,
        public string $label,
        public string $imageUrl,
    ) {
    }
}
