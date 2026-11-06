<?php

declare(strict_types=1);

namespace ZxArt\Users\UseCases;

use ZxArt\Users\Dto\UserGroupFieldsDto;

final readonly class CreateUserGroupCommand
{
    public function __construct(
        public UserGroupFieldsDto $fields,
    ) {
    }
}
