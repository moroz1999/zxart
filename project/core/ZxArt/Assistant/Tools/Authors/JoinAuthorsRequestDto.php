<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Authors;

/**
 * @psalm-api
 */
final readonly class JoinAuthorsRequestDto
{
    /**
     * @param int $mainId the author that stays
     * @param int $joinedId the author that moves into it and is deleted
     * @param bool $asAlias keep the joined author as a new alias of the main one, under its title, holding its credits
     */
    public function __construct(
        public int $mainId,
        public int $joinedId,
        public bool $asAlias = false,
    ) {
    }
}
