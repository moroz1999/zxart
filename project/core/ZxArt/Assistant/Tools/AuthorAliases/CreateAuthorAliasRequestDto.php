<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\AuthorAliases;

use ZxArt\Authors\Dto\AuthorAliasFieldsDto;

/**
 * @psalm-api
 */
final readonly class CreateAuthorAliasRequestDto
{
    /**
     * @param AuthorAliasFieldsDto $fields the fields of the new author alias; title and authorId are required
     */
    public function __construct(
        public AuthorAliasFieldsDto $fields,
    ) {
    }
}
