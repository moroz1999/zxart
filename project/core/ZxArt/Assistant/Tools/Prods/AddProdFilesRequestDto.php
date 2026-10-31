<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

use ZxArt\Prods\ProdFileKind;

/**
 * @psalm-api
 */
final readonly class AddProdFilesRequestDto
{
    /**
     * @param int $prodId the production
     * @param ProdFileKind $kind the file list to add to
     * @param list<string> $urls http or https addresses of the files
     */
    public function __construct(
        public int $prodId,
        public ProdFileKind $kind,
        public array $urls,
    ) {
    }
}
