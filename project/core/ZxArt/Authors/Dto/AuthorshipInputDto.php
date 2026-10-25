<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

/**
 * One author of an element, or one member of a group, as an edit lists them.
 * No roles stand for an unknown role; a date of 0 is no date.
 */
final readonly class AuthorshipInputDto
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $authorId,
        public array $roles = [],
        public int $startDate = 0,
        public int $endDate = 0,
    ) {
    }
}
