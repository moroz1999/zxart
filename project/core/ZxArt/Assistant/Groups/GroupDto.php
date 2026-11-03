<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Groups\Dto\GroupCountersDto;
use ZxArt\Shared\Dto\AliasDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A group as the assistant sees it: the fields its actions write, under the
 * names they take them, and what an edit needs to know besides.
 *
 * @psalm-api
 */
final readonly class GroupDto
{
    /**
     * @param list<int> $subGroupIds the groups that belong to this one
     * @param list<EntityRefDto> $refs the country, city and subgroups the ids name
     * @param list<AliasDto> $aliases
     * @param list<MembershipDto> $members the group's own; an alias's are its own
     * @param list<EntityRefDto> $parentGroups the groups this one is a subgroup of
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $abbreviation,
        public string $type,
        /** 0 when none */
        public int $countryId,
        /** 0 when none */
        public int $cityId,
        public string $website,
        public string $wikiLink,
        public string $slogan,
        /** a Unix timestamp, 0 when unknown */
        public int $startDate,
        /** a Unix timestamp, 0 when unknown */
        public int $endDate,
        public array $subGroupIds,
        public array $refs,
        public array $aliases,
        public array $members,
        public array $parentGroups,
        public GroupCountersDto $counters,
    ) {
    }
}
