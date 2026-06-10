<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyBreadcrumbRestDto;

#[Map(target: PartyBreadcrumbRestDto::class)]
readonly class PartyBreadcrumbDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
