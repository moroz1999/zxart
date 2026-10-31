<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Prods;

use ZxArt\Prods\Dto\ProdFieldsDto;

/**
 * @psalm-api
 */
final readonly class UpdateProdRequestDto
{
    /**
     * @param int $id the production
     * @param ProdFieldsDto $fields only the fields to change; a list given replaces the whole list, an empty value clears
     */
    public function __construct(
        public int $id,
        public ProdFieldsDto $fields,
    ) {
    }
}
