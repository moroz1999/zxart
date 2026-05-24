<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorLinkRestDto;

#[Map(target: AuthorLinkRestDto::class)]
readonly class AuthorLinkDto
{
    public function __construct(
        public string $url,
        public string $label,
    ) {
    }
}
