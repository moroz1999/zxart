<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\ProdImportPolicy;

final readonly class ImportProdCommand
{
    public function __construct(
        public ProdImportDTO $prod,
        public ImportOrigin $origin,
        public ProdImportPolicy $policy,
    ) {
    }
}
