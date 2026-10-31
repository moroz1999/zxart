<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Groups;

/**
 * @psalm-api
 */
final readonly class JoinGroupsRequestDto
{
    /**
     * @param int $mainId the group that stays
     * @param int $joinedId the group that moves into it and is deleted
     * @param bool $asAlias keep the joined group as a new alias of the main one, under its title, holding its members and productions
     */
    public function __construct(
        public int $mainId,
        public int $joinedId,
        public bool $asAlias = false,
    ) {
    }
}
