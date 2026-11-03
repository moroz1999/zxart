<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Authors\Dto\AuthorCountersDto;
use ZxArt\Shared\Dto\AliasDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * An author as the assistant sees it: the fields its actions write, under the
 * names they take them, and what an edit needs to know besides.
 *
 * @psalm-api
 */
final readonly class AuthorDto
{
    /**
     * @param list<EntityRefDto> $refs the country and city the ids name
     * @param list<AliasDto> $aliases
     * @param list<MembershipDto> $groups the author's own memberships; an alias's are its own
     */
    public function __construct(
        public int $id,
        /** the nickname */
        public string $title,
        public string $realName,
        /** 0 when none */
        public int $countryId,
        /** 0 when none */
        public int $cityId,
        public string $wikiLink,
        public array $refs,
        public array $aliases,
        public array $groups,
        public AuthorCountersDto $counters,
    ) {
    }
}
