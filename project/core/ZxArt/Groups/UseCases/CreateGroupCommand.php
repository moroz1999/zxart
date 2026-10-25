<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\Groups\Dto\GroupFieldsDto;

final readonly class CreateGroupCommand
{
    public function __construct(
        public GroupFieldsDto $fields,
    ) {
    }
}
