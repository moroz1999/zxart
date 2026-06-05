<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PicturePartyContextRestDto;

#[Map(target: PicturePartyContextRestDto::class)]
readonly class PicturePartyContextDto
{
    public function __construct(
        public string $title,
        public string $url,
        public ?int $place,
        public ?string $compoLabel,
    ) {
    }
}
