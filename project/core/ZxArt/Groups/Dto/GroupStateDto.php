<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Import\Dto\ImportOriginDto;

/**
 * Everything an editor may change on a group, what it is linked with, and the
 * modification time to tell whether it changed since it was read.
 *
 * @psalm-api
 */
final readonly class GroupStateDto
{
    /**
     * @param AuthorshipDto[] $members
     * @param int[] $parentGroupIds the groups this one belongs to
     * @param int[] $aliasIds
     * @param ImportOriginDto[] $importOrigins
     */
    public function __construct(
        public int $id,
        public int $modifiedAt,
        public GroupFieldsDto $fields,
        public array $members,
        public array $parentGroupIds,
        public array $aliasIds,
        public GroupCountersDto $counters,
        public array $importOrigins,
    ) {
    }
}
