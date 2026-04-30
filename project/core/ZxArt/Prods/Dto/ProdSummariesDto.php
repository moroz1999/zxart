<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdSummariesDto
{
    /**
     * @param ProdSummaryDto[] $prods
     */
    public function __construct(
        public array $prods,
    ) {
    }
}
