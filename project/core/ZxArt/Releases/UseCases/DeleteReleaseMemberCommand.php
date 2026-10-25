<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

final readonly class DeleteReleaseMemberCommand
{
    public function __construct(
        public int $releaseId,
        public int $authorId,
    ) {
    }
}
