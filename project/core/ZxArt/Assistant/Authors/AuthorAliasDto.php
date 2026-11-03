<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Authors\Dto\AuthorCountersDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * An author alias as the assistant sees it: the fields its actions write, under
 * the names they take them, and what an edit needs to know besides.
 *
 * @psalm-api
 */
final readonly class AuthorAliasDto
{
    /**
     * @param list<EntityRefDto> $refs the author the id names
     * @param list<MembershipDto> $groups
     */
    public function __construct(
        public int $id,
        public string $title,
        public int $authorId,
        /** a Unix timestamp, 0 when unknown */
        public int $startDate,
        /** a Unix timestamp, 0 when unknown */
        public int $endDate,
        public array $refs,
        public array $groups,
        public AuthorCountersDto $counters,
    ) {
    }
}
