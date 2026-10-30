<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A group alias as an edit needs to see it.
 *
 * @psalm-api
 */
final readonly class GroupAliasCardDto
{
    /**
     * @param list<EntityRefDto> $refs the entities the fields name by id
     * @param list<MembershipDto> $members
     */
    public function __construct(
        public int $id,
        /** the fields as `update_group_alias` takes them */
        public GroupAliasFieldsDto $fields,
        public array $refs,
        public array $members,
        public GroupCountersDto $counters,
    ) {
    }
}
