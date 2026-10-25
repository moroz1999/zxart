<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\Authors\Dto\AuthorFieldsDto;

final readonly class CreateAuthorCommand
{
    public function __construct(
        public AuthorFieldsDto $fields,
    ) {
    }
}
