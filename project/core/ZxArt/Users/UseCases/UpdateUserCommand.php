<?php

declare(strict_types=1);

namespace ZxArt\Users\UseCases;

use ZxArt\Users\Dto\UserFieldsDto;

final readonly class UpdateUserCommand
{
    public function __construct(
        public int $userId,
        public UserFieldsDto $fields,
    ) {
    }
}
