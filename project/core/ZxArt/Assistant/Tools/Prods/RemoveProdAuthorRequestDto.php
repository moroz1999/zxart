<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

/**
 * @psalm-api
 */
final readonly class RemoveProdAuthorRequestDto
{
    /**
     * @param int $prodId the production
     * @param int $authorId the credited author or author alias
     */
    public function __construct(
        public int $prodId,
        public int $authorId,
    ) {
    }
}
