<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\AuthorAliases;

/**
 * @psalm-api
 */
final readonly class JoinAuthorAliasesRequestDto
{
    /**
     * @param int $mainId the author alias that stays
     * @param int $joinedId the author alias that moves into it and is deleted
     */
    public function __construct(
        public int $mainId,
        public int $joinedId,
    ) {
    }
}
