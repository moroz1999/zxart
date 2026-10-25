<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\Groups\Dto\GroupAliasFieldsDto;

final readonly class CreateGroupAliasCommand
{
    public function __construct(
        public GroupAliasFieldsDto $fields,
    ) {
    }
}
