<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Groups\Dto\GroupCountersDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A group alias as the assistant sees it: the fields its actions write, under
 * the names they take them, and what an edit needs to know besides.
 *
 * @psalm-api
 */
final readonly class GroupAliasDto
{
    /**
     * @param list<EntityRefDto> $refs the group the id names
     * @param list<MembershipDto> $members
     */
    public function __construct(
        public int $id,
        public string $title,
        public int $groupId,
        /** a Unix timestamp, 0 when unknown */
        public int $startDate,
        /** a Unix timestamp, 0 when unknown */
        public int $endDate,
        public array $refs,
        public array $members,
        public GroupCountersDto $counters,
    ) {
    }
}
