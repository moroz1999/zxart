<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

/**
 * @psalm-api
 */
final readonly class RemoveReleaseAuthorRequestDto
{
    /**
     * @param int $releaseId the release
     * @param int $authorId the credited author or author alias
     */
    public function __construct(
        public int $releaseId,
        public int $authorId,
    ) {
    }
}
