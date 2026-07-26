<?php

declare(strict_types=1);

namespace ZxArt\Press\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Press\Rest\PressTagRestDto;

#[Map(target: PressTagRestDto::class)]
readonly class PressTagDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
