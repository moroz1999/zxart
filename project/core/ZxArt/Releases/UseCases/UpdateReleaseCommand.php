<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Dto\ReleaseFileUploadDto;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * A list left `null` keeps what the release holds; an empty list clears it.
 * The uploaded files are added to the lists of their kinds; `file` replaces
 * the release's own file; `removeFile` takes it away when no file is given.
 */
final readonly class UpdateReleaseCommand
{
    /**
     * @param list<AuthorshipInputDto>|null $authors
     * @param list<ImportOriginDto>|null $importOrigins
     * @param list<ReleaseFileUploadDto> $files
     */
    public function __construct(
        public int $releaseId,
        public ReleaseFieldsDto $fields,
        public ?array $authors = null,
        public ?array $importOrigins = null,
        public array $files = [],
        public ?UploadedFileDto $file = null,
        public bool $removeFile = false,
    ) {
    }
}
