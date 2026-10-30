<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * An author alias as an edit needs to see it.
 *
 * @psalm-api
 */
final readonly class AuthorAliasCardDto
{
    /**
     * @param list<EntityRefDto> $refs the entities the fields name by id
     * @param list<MembershipDto> $memberships
     */
    public function __construct(
        public int $id,
        /** the fields as `update_author_alias` takes them */
        public AuthorAliasFieldsDto $fields,
        public array $refs,
        public array $memberships,
        public AuthorCountersDto $counters,
    ) {
    }
}
