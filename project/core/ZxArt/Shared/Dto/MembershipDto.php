<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

use ZxArt\Shared\References\EntityRefDto;

/**
 * A membership seen from one side: the group of an author, or the member of a
 * group. Dates are Unix timestamps, 0 when unknown.
 *
 * @psalm-api
 */
final readonly class MembershipDto
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public EntityRefDto $entity,
        public array $roles,
        public int $startDate,
        public int $endDate,
    ) {
    }
}
