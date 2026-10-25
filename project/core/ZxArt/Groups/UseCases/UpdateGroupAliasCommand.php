<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\Groups\Dto\GroupAliasFieldsDto;

final readonly class UpdateGroupAliasCommand
{
    /**
     * @param GroupAliasFieldsDto $fields only the fields it carries change
     */
    public function __construct(
        public int $aliasId,
        public GroupAliasFieldsDto $fields,
    ) {
    }
}
