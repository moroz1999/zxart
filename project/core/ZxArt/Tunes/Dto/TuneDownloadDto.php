<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Tunes\Rest\TuneDownloadRestDto;

#[Map(target: TuneDownloadRestDto::class)]
readonly class TuneDownloadDto
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
