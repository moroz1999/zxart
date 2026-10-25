<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

/**
 * One group or production an author takes part in, as the author's edit lists
 * it. No roles stand for an unknown role; a date of 0 is no date.
 */
final readonly class MembershipInputDto
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $elementId,
        public array $roles = [],
        public int $startDate = 0,
        public int $endDate = 0,
    ) {
    }
}
