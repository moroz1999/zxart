<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\AuthorAliases;

use ZxArt\Authors\Dto\AuthorAliasFieldsDto;

/**
 * @psalm-api
 */
final readonly class UpdateAuthorAliasRequestDto
{
    /**
     * @param int $id the author alias
     * @param AuthorAliasFieldsDto $fields only the fields to change; a list given replaces the whole list, an empty value clears
     */
    public function __construct(
        public int $id,
        public AuthorAliasFieldsDto $fields,
    ) {
    }
}
