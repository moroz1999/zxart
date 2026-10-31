<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

/**
 * @psalm-api
 */
final readonly class JoinProdsRequestDto
{
    /**
     * @param int $mainId the production that stays
     * @param int $joinedId the production that moves into it and is deleted
     * @param bool $releasesOnly move only the releases and materials, not the authors, groups, publishers, categories and fields
     */
    public function __construct(
        public int $mainId,
        public int $joinedId,
        public bool $releasesOnly = false,
    ) {
    }
}
