<?php

declare(strict_types=1);

namespace ZxArt\PictureSearch\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\PictureSearch\Rest\LocationRestDto;

#[Map(target: LocationRestDto::class)]
readonly class LocationDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
