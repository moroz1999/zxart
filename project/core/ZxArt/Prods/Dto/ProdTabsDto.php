<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdTabsRestDto;

#[Map(target: ProdTabsRestDto::class)]
readonly class ProdTabsDto
{
    public function __construct(
        public bool $hasScreenshots,
        public bool $hasInlays,
        public bool $hasMaps,
        public bool $hasRzx,
        public bool $hasPictures,
        public bool $hasTunes,
        public bool $hasArticles,
        public bool $hasSeries,
        public bool $hasCompilations,
    ) {
    }
}
