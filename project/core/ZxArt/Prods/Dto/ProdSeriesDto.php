<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdSeriesDto
{
    /**
     * @param ProdSeriesEntryDto[] $series
     */
    public function __construct(
        public array $series,
    ) {
    }
}
