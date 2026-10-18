<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

use ZxArt\Pictures\PictureDownloadKind;

readonly class PictureDownloadRestDto
{
    public function __construct(
        public string $id,
        public PictureDownloadKind $kind,
        public string $ext,
        public ?int $zoom,
        public ?string $fileName,
        public ?string $size,
        public string $url,
    ) {
    }
}
