<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupBreadcrumbRestDto;

#[Map(target: GroupBreadcrumbRestDto::class)]
readonly class GroupBreadcrumbDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
