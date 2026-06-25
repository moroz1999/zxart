<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class StatsTopUsersRestDto
{
    /**
     * @param StatsTopUserRestDto[] $users
     */
    public function __construct(
        public string $unitKey,
        #[Map(transform: new MapCollection())]
        public array $users,
    ) {
    }
}
