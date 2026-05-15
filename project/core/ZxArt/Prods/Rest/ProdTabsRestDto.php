<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

readonly class ProdTabsRestDto
{
    public function __construct(
        public bool $hasReleases,
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
