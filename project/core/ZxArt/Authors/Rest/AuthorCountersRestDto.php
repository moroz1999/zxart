<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorCountersRestDto
{
    public function __construct(
        public int $pictures,
        public int $tunes,
        public int $prods,
        public int $comments,
    ) {
    }
}
