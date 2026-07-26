<?php

declare(strict_types=1);

namespace ZxArt\Press\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Press\Rest\PressMentionRestDto;

#[Map(target: PressMentionRestDto::class)]
readonly class PressMentionDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
