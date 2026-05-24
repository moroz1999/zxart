<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorCountersRestDto;

#[Map(target: AuthorCountersRestDto::class)]
readonly class AuthorCountersDto
{
    public function __construct(
        public int $pictures,
        public int $tunes,
        public int $prods,
        public int $comments,
    ) {
    }
}
