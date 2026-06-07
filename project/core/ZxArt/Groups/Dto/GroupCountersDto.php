<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupCountersRestDto;

#[Map(target: GroupCountersRestDto::class)]
readonly class GroupCountersDto
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
