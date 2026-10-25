<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

/**
 * One authorship record: an author's part in a production or a release, or a
 * membership in a group. A date not set is 0.
 *
 * @psalm-api
 */
final readonly class AuthorshipDto
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $elementId,
        public int $authorId,
        public array $roles,
        public int $startDate,
        public int $endDate,
    ) {
    }
}
