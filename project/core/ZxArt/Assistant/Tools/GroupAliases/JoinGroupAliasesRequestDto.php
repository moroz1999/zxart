<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

/**
 * @psalm-api
 */
final readonly class JoinGroupAliasesRequestDto
{
    /**
     * @param int $mainId the group alias that stays
     * @param int $joinedId the group alias that moves into it and is deleted
     */
    public function __construct(
        public int $mainId,
        public int $joinedId,
    ) {
    }
}
