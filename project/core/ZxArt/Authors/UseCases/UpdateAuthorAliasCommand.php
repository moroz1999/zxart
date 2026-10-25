<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\Authors\Dto\AuthorAliasFieldsDto;

final readonly class UpdateAuthorAliasCommand
{
    /**
     * @param AuthorAliasFieldsDto $fields only the fields it carries change
     */
    public function __construct(
        public int $aliasId,
        public AuthorAliasFieldsDto $fields,
    ) {
    }
}
