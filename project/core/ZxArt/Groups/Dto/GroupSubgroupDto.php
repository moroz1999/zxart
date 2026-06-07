<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupSubgroupRestDto;

#[Map(target: GroupSubgroupRestDto::class)]
readonly class GroupSubgroupDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $abbreviation,
        public string $url,
        public int $membersCount,
        public int $prodsCount,
        public ?string $years,
    ) {
    }
}
