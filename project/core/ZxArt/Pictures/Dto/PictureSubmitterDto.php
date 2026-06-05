<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureSubmitterRestDto;

#[Map(target: PictureSubmitterRestDto::class)]
readonly class PictureSubmitterDto
{
    public function __construct(
        public string $userName,
        public string $url,
    ) {
    }
}
