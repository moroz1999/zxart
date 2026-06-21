<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dto;

readonly class StatsUsersSectionDto
{
    /**
     * @param StatsTopUserDto[] $voters
     * @param StatsTopUserDto[] $comments
     * @param StatsTopUserDto[] $tags
     */
    public function __construct(
        public array $voters,
        public array $comments,
        public array $tags,
    ) {
    }
}
