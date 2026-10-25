<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

final readonly class DeleteReleaseCommand
{
    public function __construct(
        public int $releaseId,
    ) {
    }
}
