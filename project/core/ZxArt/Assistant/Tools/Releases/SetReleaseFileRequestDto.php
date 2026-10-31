<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

/**
 * @psalm-api
 */
final readonly class SetReleaseFileRequestDto
{
    /**
     * @param int $releaseId the release
     * @param string $url an http or https address of the file
     * @param ?string $fileName the name to store it under; null takes the name from the URL
     */
    public function __construct(
        public int $releaseId,
        public string $url,
        public ?string $fileName = null,
    ) {
    }
}
