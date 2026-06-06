<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Tunes\Rest\TuneSubmitterRestDto;

#[Map(target: TuneSubmitterRestDto::class)]
readonly class TuneSubmitterDto
{
    public function __construct(
        public string $userName,
        public string $url,
    ) {
    }
}
