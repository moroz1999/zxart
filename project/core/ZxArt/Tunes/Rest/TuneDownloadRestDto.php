<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Rest;

use ZxArt\Tunes\TuneDownloadKind;

readonly class TuneDownloadRestDto
{
    public function __construct(
        public string $id,
        public TuneDownloadKind $kind,
        public string $ext,
        public ?string $fileName,
        public ?string $size,
        public string $url,
    ) {
    }
}
