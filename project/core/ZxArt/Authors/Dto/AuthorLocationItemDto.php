<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorLocationItemRestDto;

#[Map(target: AuthorLocationItemRestDto::class)]
readonly class AuthorLocationItemDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
