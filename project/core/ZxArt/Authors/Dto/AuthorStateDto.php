<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use ZxArt\Import\Dto\ImportOriginDto;

/**
 * Everything an editor may change on an author, what the author is linked
 * with, and the modification time to tell whether it changed since it was
 * read.
 *
 * @psalm-api
 */
final readonly class AuthorStateDto
{
    /**
     * @param int[] $aliasIds
     * @param AuthorshipDto[] $memberships the groups and group aliases the author belongs to
     * @param ImportOriginDto[] $importOrigins
     */
    public function __construct(
        public int $id,
        public int $modifiedAt,
        public AuthorFieldsDto $fields,
        public array $aliasIds,
        public array $memberships,
        public AuthorCountersDto $counters,
        public array $importOrigins,
    ) {
    }
}
