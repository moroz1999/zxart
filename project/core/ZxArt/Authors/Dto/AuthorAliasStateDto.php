<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use ZxArt\Import\Dto\ImportOriginDto;

/**
 * Everything an editor may change on an author alias, what it is linked with,
 * and the modification time to tell whether it changed since it was read.
 *
 * @psalm-api
 */
final readonly class AuthorAliasStateDto
{
    /**
     * @param AuthorshipDto[] $memberships the groups and group aliases the alias belongs to
     * @param ImportOriginDto[] $importOrigins
     */
    public function __construct(
        public int $id,
        public int $modifiedAt,
        public AuthorAliasFieldsDto $fields,
        public array $memberships,
        public AuthorCountersDto $counters,
        public array $importOrigins,
    ) {
    }
}
