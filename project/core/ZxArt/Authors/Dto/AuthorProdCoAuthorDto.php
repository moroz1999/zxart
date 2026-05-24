<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorProdCoAuthorRestDto;

#[Map(target: AuthorProdCoAuthorRestDto::class)]
readonly class AuthorProdCoAuthorDto
{
    public function __construct(
        public string $name,
        public string $url,
    ) {
    }
}
