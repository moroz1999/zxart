<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Tunes\Rest\TuneTagRestDto;

#[Map(target: TuneTagRestDto::class)]
readonly class TuneTagDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
