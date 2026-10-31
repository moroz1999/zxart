<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

use ZxArt\Groups\Dto\GroupAliasFieldsDto;

/**
 * @psalm-api
 */
final readonly class UpdateGroupAliasRequestDto
{
    /**
     * @param int $id the group alias
     * @param GroupAliasFieldsDto $fields only the fields to change; a list given replaces the whole list, an empty value clears
     */
    public function __construct(
        public int $id,
        public GroupAliasFieldsDto $fields,
    ) {
    }
}
