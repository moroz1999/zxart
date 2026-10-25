<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

final readonly class DeleteProdFileCommand
{
    public function __construct(
        public int $prodId,
        public int $fileId,
    ) {
    }
}
