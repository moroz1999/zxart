<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Dto\ReleaseFileUploadDto;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * Values typed once for a release per uploaded file.
 */
final readonly class CreateReleasesFromFilesCommand
{
    /**
     * @param list<UploadedFileDto> $releaseFiles one release per file; none makes the one release described
     * @param list<AuthorshipInputDto> $authors
     * @param list<ReleaseFileUploadDto> $files added to every release
     */
    public function __construct(
        public ReleaseFieldsDto $fields,
        public array $releaseFiles,
        public array $authors = [],
        public array $files = [],
    ) {
    }
}
