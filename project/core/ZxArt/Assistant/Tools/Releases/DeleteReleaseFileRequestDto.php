<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

/**
 * @psalm-api
 */
final readonly class DeleteReleaseFileRequestDto
{
    /**
     * @param int $releaseId the release
     * @param int $fileId the file, as get_release lists it
     */
    public function __construct(
        public int $releaseId,
        public int $fileId,
    ) {
    }
}
