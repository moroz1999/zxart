<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Tunes\Rest\TuneDownloadRestDto;
use ZxArt\Tunes\TuneDownloadKind;

#[Map(target: TuneDownloadRestDto::class)]
readonly class TuneDownloadDto
{
    public function __construct(
        public string $id,
        public TuneDownloadKind $kind,
        public string $ext,
        /** The uploaded file's own name. */
        public ?string $fileName,
        public ?string $size,
        public string $url,
    ) {
    }
}
