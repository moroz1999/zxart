<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorGroupRestDto;

#[Map(target: AuthorGroupRestDto::class)]
readonly class AuthorGroupDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $years,
    ) {
    }
}
