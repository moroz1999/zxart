<?php

declare(strict_types=1);

namespace ZxArt\Stats\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class StatsUsersSectionRestDto
{
    /**
     * @param StatsTopUserRestDto[] $voters
     * @param StatsTopUserRestDto[] $comments
     * @param StatsTopUserRestDto[] $tags
     */
    public function __construct(
        #[Map(transform: MapCollection::class)]
        public array $voters,
        #[Map(transform: MapCollection::class)]
        public array $comments,
        #[Map(transform: MapCollection::class)]
        public array $tags,
    ) {
    }
}
