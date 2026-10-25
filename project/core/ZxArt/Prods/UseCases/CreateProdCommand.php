<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\Prods\Dto\ProdFieldsDto;

final readonly class CreateProdCommand
{
    public function __construct(
        public ProdFieldsDto $fields,
    ) {
    }
}
