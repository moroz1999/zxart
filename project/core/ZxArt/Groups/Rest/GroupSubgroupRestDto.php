<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

readonly class GroupSubgroupRestDto
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
