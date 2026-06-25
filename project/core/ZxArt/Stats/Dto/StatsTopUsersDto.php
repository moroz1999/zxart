<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Stats\Rest\StatsTopUsersRestDto;

#[Map(target: StatsTopUsersRestDto::class)]
readonly class StatsTopUsersDto
{
    /**
     * @param StatsTopUserDto[] $users
     */
    public function __construct(
        public string $unitKey,
        #[Map(transform: new MapCollection())]
        public array $users,
    ) {
    }
}
