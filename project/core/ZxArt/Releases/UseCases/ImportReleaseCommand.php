<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Import\Prods\ProdImportPolicy;

final readonly class ImportReleaseCommand
{
    /**
     * @param string $prodImportId the source's id of the release's production,
     *     imported before it
     */
    public function __construct(
        public ReleaseImportDTO $release,
        public string $prodImportId,
        public ImportOrigin $origin,
        public ProdImportPolicy $policy,
    ) {
    }
}
