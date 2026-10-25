<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

final readonly class SetReleaseFileCommand
{
    /**
     * @param string|null $fileName null or empty for the name in the URL
     */
    public function __construct(
        public int $releaseId,
        public string $url,
        public ?string $fileName,
    ) {
    }
}
