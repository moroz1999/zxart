<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\Groups\Dto\GroupFieldsDto;

final readonly class UpdateGroupCommand
{
    /**
     * @param GroupFieldsDto $fields only the fields it carries change
     */
    public function __construct(
        public int $groupId,
        public GroupFieldsDto $fields,
    ) {
    }
}
