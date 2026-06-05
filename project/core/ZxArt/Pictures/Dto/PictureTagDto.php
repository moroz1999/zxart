<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureTagRestDto;

#[Map(target: PictureTagRestDto::class)]
readonly class PictureTagDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
