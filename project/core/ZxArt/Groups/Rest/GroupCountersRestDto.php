<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

readonly class GroupCountersRestDto
{
    public function __construct(
        public int $members,
        public int $subgroups,
        public int $prods,
        public int $published,
        public int $releases,
        public int $mentions,
        public int $comments,
    ) {
    }
}
