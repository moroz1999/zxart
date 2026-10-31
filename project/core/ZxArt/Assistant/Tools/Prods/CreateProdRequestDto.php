<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

use ZxArt\Prods\Dto\ProdFieldsDto;

/**
 * @psalm-api
 */
final readonly class CreateProdRequestDto
{
    /**
     * @param ProdFieldsDto $fields the fields of the new production; title is required
     */
    public function __construct(
        public ProdFieldsDto $fields,
    ) {
    }
}
