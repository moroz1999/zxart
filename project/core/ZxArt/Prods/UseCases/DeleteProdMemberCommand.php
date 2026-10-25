<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

final readonly class DeleteProdMemberCommand
{
    public function __construct(
        public int $prodId,
        public int $authorId,
    ) {
    }
}
