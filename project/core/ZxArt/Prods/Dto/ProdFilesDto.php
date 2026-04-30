<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdFilesDto
{
    /**
     * @param ProdFileDto[] $files
     */
    public function __construct(
        public array $files,
    ) {
    }
}
