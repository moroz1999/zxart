<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Rest;

readonly class TuneDownloadRestDto
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
