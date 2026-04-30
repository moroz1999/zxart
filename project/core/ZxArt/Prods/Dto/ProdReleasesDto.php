<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdReleasesDto
{
    /**
     * @param ProdReleaseDto[] $releases
     */
    public function __construct(
        public array $releases,
    ) {
    }
}
