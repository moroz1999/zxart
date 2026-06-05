<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureProdContextRestDto;

#[Map(target: PictureProdContextRestDto::class)]
readonly class PictureProdContextDto
{
    public function __construct(
        public string $title,
        public string $url,
        public ?string $year,
        public string $kindLabel,
    ) {
    }
}
