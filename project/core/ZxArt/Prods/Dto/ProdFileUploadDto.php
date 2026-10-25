<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Prods\ProdFileKind;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * An uploaded file for one of a production's file lists.
 */
final readonly class ProdFileUploadDto
{
    public function __construct(
        public ProdFileKind $kind,
        public UploadedFileDto $file,
    ) {
    }
}
