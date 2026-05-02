<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdSeriesEntryRestDto;

#[Map(target: ProdSeriesEntryRestDto::class)]
readonly class ProdSeriesEntryDto
{
    /**
     * @param ProdSummaryDto[] $prods
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public array $prods,
    ) {
    }
}
