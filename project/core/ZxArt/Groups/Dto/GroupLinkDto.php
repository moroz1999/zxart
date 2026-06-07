<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupLinkRestDto;

#[Map(target: GroupLinkRestDto::class)]
readonly class GroupLinkDto
{
    public function __construct(
        public string $url,
        public string $label,
    ) {
    }
}
