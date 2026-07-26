<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorAliasCreatedRestDto;

#[Map(target: AuthorAliasCreatedRestDto::class)]
final readonly class AuthorAliasCreatedDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
