<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Groups;

use ZxArt\Groups\Dto\GroupFieldsDto;

/**
 * @psalm-api
 */
final readonly class CreateGroupRequestDto
{
    /**
     * @param GroupFieldsDto $fields the fields of the new group; title is required
     */
    public function __construct(
        public GroupFieldsDto $fields,
    ) {
    }
}
