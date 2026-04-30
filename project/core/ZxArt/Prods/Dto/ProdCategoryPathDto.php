<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdCategoryPathDto
{
    /**
     * @param ProdCategoryRefDto[] $categories
     */
    public function __construct(
        public array $categories,
    ) {
    }
}
