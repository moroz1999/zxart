<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Pictures\Rest\PictureMentionRestDto;

#[Map(target: PictureMentionRestDto::class)]
readonly class PictureMentionDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
