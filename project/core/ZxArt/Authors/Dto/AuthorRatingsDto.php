<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorRatingsRestDto;

#[Map(target: AuthorRatingsRestDto::class)]
readonly class AuthorRatingsDto
{
    public function __construct(
        public float $artist,
        public float $musician,
    ) {
    }
}
