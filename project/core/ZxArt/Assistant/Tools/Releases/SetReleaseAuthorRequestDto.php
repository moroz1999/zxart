<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

/**
 * @psalm-api
 */
final readonly class SetReleaseAuthorRequestDto
{
    /**
     * @param int $releaseId the release
     * @param int $authorId an author or an author alias
     * @param list<string> $roles roles from list_options; they replace the author's roles, none stands for unknown
     */
    public function __construct(
        public int $releaseId,
        public int $authorId,
        public array $roles,
    ) {
    }
}
