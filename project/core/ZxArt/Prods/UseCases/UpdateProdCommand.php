<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\Prods\Dto\ProdFieldsDto;

final readonly class UpdateProdCommand
{
    /**
     * @param ProdFieldsDto $fields only the fields it carries change
     */
    public function __construct(
        public int $prodId,
        public ProdFieldsDto $fields,
    ) {
    }
}
