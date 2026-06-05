<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureDownloadRestDto;

#[Map(target: PictureDownloadRestDto::class)]
readonly class PictureDownloadDto
{
    public function __construct(
        public string $id,
        public string $ext,
        public string $label,
        public ?string $sub,
        public ?string $size,
        public string $url,
    ) {
    }
}
