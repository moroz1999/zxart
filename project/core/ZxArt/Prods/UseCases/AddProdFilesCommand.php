<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\Prods\ProdFileKind;

final readonly class AddProdFilesCommand
{
    /**
     * @param string[] $urls
     */
    public function __construct(
        public int $prodId,
        public ProdFileKind $kind,
        public array $urls,
    ) {
    }
}
