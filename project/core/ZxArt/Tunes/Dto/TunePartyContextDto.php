<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Tunes\Rest\TunePartyContextRestDto;

#[Map(target: TunePartyContextRestDto::class)]
readonly class TunePartyContextDto
{
    public function __construct(
        public string $title,
        public string $url,
        public ?int $place,
        public ?string $compoLabel,
    ) {
    }
}
