<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Import\Dto\ImportOriginDto;

/**
 * Everything an editor may change on a group alias, what it is linked with,
 * and the modification time to tell whether it changed since it was read.
 *
 * @psalm-api
 */
final readonly class GroupAliasStateDto
{
    /**
     * @param AuthorshipDto[] $members
     * @param ImportOriginDto[] $importOrigins
     */
    public function __construct(
        public int $id,
        public int $modifiedAt,
        public GroupAliasFieldsDto $fields,
        public array $members,
        public GroupCountersDto $counters,
        public array $importOrigins,
    ) {
    }
}
