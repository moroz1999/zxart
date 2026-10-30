<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use ZxArt\Shared\Dto\AliasDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A group as an edit needs to see it.
 *
 * @psalm-api
 */
final readonly class GroupCardDto
{
    /**
     * @param list<EntityRefDto> $refs the entities the fields name by id
     * @param list<MembershipDto> $members the group's own; an alias's are on its card
     * @param list<EntityRefDto> $parentGroups the groups this one is a subgroup of
     * @param list<AliasDto> $aliases
     */
    public function __construct(
        public int $id,
        /** the fields as `update_group` takes them */
        public GroupFieldsDto $fields,
        public array $refs,
        public array $members,
        public array $parentGroups,
        public array $aliases,
        public GroupCountersDto $counters,
    ) {
    }
}
