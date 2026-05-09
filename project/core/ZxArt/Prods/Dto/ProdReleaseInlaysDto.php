<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdReleaseInlaysDto
{
    /**
     * @param ProdReleaseInlayDto[] $inlays
     */
    public function __construct(
        public array $inlays,
    ) {
    }
}
