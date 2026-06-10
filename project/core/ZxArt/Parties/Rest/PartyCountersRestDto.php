<?php

declare(strict_types=1);

namespace ZxArt\Parties\Rest;

readonly class PartyCountersRestDto
{
    public function __construct(
        public int $compos,
        public int $entries,
        public int $authors,
        public int $pictures,
        public int $tunes,
        public int $prods,
        public int $comments,
    ) {
    }
}
