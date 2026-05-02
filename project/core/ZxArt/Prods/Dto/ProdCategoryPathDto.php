<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdCategoryPathRestDto;

#[Map(target: ProdCategoryPathRestDto::class)]
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
