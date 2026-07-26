<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorAliasFormRestDto;

#[Map(target: AuthorAliasFormRestDto::class)]
final readonly class AuthorAliasFormDto
{
    public function __construct(
        public AuthorAliasFormAuthorDto $author,
    ) {
    }
}
