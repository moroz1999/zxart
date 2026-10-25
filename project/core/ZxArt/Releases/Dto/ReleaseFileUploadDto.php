<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * An uploaded file for one of a release's file lists.
 */
final readonly class ReleaseFileUploadDto
{
    public function __construct(
        public ReleaseFileKind $kind,
        public UploadedFileDto $file,
    ) {
    }
}
