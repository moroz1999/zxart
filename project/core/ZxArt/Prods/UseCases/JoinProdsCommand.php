<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

final readonly class JoinProdsCommand
{
    /**
     * @param int $prodId the production that stays
     * @param int $joinedId the production that moves into it and is deleted
     * @param bool $releasesOnly move only the releases and materials, not the authors, groups, categories and fields
     */
    public function __construct(
        public int $prodId,
        public int $joinedId,
        public bool $releasesOnly = false,
    ) {
    }
}
