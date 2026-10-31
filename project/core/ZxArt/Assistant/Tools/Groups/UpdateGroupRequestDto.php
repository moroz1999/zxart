<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Groups;

use ZxArt\Groups\Dto\GroupFieldsDto;

/**
 * @psalm-api
 */
final readonly class UpdateGroupRequestDto
{
    /**
     * @param int $id the group
     * @param GroupFieldsDto $fields only the fields to change; a list given replaces the whole list, an empty value clears
     */
    public function __construct(
        public int $id,
        public GroupFieldsDto $fields,
    ) {
    }
}
