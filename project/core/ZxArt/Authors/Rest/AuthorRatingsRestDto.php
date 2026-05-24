<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorRatingsRestDto
{
    public function __construct(
        public float $artist,
        public float $musician,
    ) {
    }
}
