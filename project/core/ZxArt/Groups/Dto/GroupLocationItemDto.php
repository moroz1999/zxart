<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupLocationItemRestDto;

#[Map(target: GroupLocationItemRestDto::class)]
readonly class GroupLocationItemDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
