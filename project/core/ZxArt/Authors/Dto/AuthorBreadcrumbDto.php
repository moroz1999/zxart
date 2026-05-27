<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorBreadcrumbRestDto;

#[Map(target: AuthorBreadcrumbRestDto::class)]
readonly class AuthorBreadcrumbDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
