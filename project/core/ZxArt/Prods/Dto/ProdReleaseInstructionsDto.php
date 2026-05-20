<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdReleaseInstructionsDto
{
    /**
     * @param ProdReleaseInstructionFileDto[] $files
     */
    public function __construct(
        public array $files,
    ) {
    }
}
