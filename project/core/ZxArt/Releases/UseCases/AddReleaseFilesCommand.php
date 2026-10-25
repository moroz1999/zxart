<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\Releases\ReleaseFileKind;

final readonly class AddReleaseFilesCommand
{
    /**
     * @param string[] $urls
     */
    public function __construct(
        public int $releaseId,
        public ReleaseFileKind $kind,
        public array $urls,
    ) {
    }
}
