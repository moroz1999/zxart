<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;
use ZxArt\Stats\Rest\StatsUsersSectionRestDto;

#[Map(target: StatsUsersSectionRestDto::class)]
readonly class StatsUsersSectionDto
{
    /**
     * @param StatsTopUserDto[] $voters
     * @param StatsTopUserDto[] $comments
     * @param StatsTopUserDto[] $tags
     */
    public function __construct(
        #[Map(transform: new MapCollection())]
        public array $voters,
        #[Map(transform: new MapCollection())]
        public array $comments,
        #[Map(transform: new MapCollection())]
        public array $tags,
    ) {
    }
}
