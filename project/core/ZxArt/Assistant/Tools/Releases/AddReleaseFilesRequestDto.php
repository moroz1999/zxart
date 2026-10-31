<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

use ZxArt\Releases\ReleaseFileKind;

/**
 * @psalm-api
 */
final readonly class AddReleaseFilesRequestDto
{
    /**
     * @param int $releaseId the release
     * @param ReleaseFileKind $kind the file list to add to
     * @param list<string> $urls http or https addresses of the files
     */
    public function __construct(
        public int $releaseId,
        public ReleaseFileKind $kind,
        public array $urls,
    ) {
    }
}
