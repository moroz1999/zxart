<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

final readonly class SetReleaseMemberCommand
{
    /**
     * @param list<string> $roles none stands for an unknown role
     */
    public function __construct(
        public int $releaseId,
        public int $authorId,
        public array $roles,
    ) {
    }
}
