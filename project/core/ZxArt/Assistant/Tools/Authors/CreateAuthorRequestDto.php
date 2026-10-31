<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Authors;

use ZxArt\Authors\Dto\AuthorFieldsDto;

/**
 * @psalm-api
 */
final readonly class CreateAuthorRequestDto
{
    /**
     * @param AuthorFieldsDto $fields the fields of the new author; title is required
     */
    public function __construct(
        public AuthorFieldsDto $fields,
    ) {
    }
}
