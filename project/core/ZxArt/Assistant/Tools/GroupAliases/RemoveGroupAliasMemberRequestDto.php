<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

/**
 * @psalm-api
 */
final readonly class RemoveGroupAliasMemberRequestDto
{
    /**
     * @param int $aliasId the group alias
     * @param int $authorId the credited author or author alias
     */
    public function __construct(
        public int $aliasId,
        public int $authorId,
    ) {
    }
}
