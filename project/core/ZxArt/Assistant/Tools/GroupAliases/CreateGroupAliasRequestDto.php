<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

use ZxArt\Groups\Dto\GroupAliasFieldsDto;

/**
 * @psalm-api
 */
final readonly class CreateGroupAliasRequestDto
{
    /**
     * @param GroupAliasFieldsDto $fields the fields of the new group alias; title and groupId are required
     */
    public function __construct(
        public GroupAliasFieldsDto $fields,
    ) {
    }
}
