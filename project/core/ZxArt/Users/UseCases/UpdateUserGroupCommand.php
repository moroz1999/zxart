<?php

declare(strict_types=1);

namespace ZxArt\Users\UseCases;

use ZxArt\Users\Dto\UserGroupFieldsDto;

final readonly class UpdateUserGroupCommand
{
    public function __construct(
        public int $groupId,
        public UserGroupFieldsDto $fields,
    ) {
    }
}
