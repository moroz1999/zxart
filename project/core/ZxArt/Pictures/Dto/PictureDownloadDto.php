<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\PictureDownloadKind;
use ZxArt\Pictures\Rest\PictureDownloadRestDto;

#[Map(target: PictureDownloadRestDto::class)]
readonly class PictureDownloadDto
{
    public function __construct(
        public string $id,
        public PictureDownloadKind $kind,
        public string $ext,
        /** Magnification of a PC render. */
        public ?int $zoom,
        /** The uploaded file's own name, for materials. */
        public ?string $fileName,
        public ?string $size,
        public string $url,
    ) {
    }
}
