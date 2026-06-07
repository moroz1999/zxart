<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupRefRestDto;

#[Map(target: GroupRefRestDto::class)]
readonly class GroupRefDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $years,
    ) {
    }
}
