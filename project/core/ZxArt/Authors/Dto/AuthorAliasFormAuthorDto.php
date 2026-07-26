<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorAliasFormAuthorRestDto;

#[Map(target: AuthorAliasFormAuthorRestDto::class)]
final readonly class AuthorAliasFormAuthorDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
