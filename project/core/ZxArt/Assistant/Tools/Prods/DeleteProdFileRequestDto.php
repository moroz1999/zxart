<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

/**
 * @psalm-api
 */
final readonly class DeleteProdFileRequestDto
{
    /**
     * @param int $prodId the production
     * @param int $fileId the file, as get_prod lists it
     */
    public function __construct(
        public int $prodId,
        public int $fileId,
    ) {
    }
}
