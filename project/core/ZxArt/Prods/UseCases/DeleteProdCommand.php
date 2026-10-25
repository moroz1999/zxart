<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

final readonly class DeleteProdCommand
{
    public function __construct(
        public int $prodId,
    ) {
    }
}
