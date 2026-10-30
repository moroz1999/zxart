<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use ZxArt\Shared\Dto\AliasDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * An author as an edit needs to see it.
 *
 * @psalm-api
 */
final readonly class AuthorCardDto
{
    /**
     * @param list<EntityRefDto> $refs the entities the fields name by id
     * @param list<AliasDto> $aliases
     * @param list<MembershipDto> $memberships the author's own; an alias's are on its card
     */
    public function __construct(
        public int $id,
        /** the fields as `update_author` takes them */
        public AuthorFieldsDto $fields,
        public array $refs,
        public array $aliases,
        public array $memberships,
        public AuthorCountersDto $counters,
    ) {
    }
}
