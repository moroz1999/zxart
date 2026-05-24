<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorAliasRefRestDto;

#[Map(target: AuthorAliasRefRestDto::class)]
readonly class AuthorAliasRefDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {
    }
}
