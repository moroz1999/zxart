<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\Authors\Dto\AuthorAliasFieldsDto;

final readonly class CreateAuthorAliasCommand
{
    public function __construct(
        public AuthorAliasFieldsDto $fields,
    ) {
    }
}
