<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Authors;

use ZxArt\Authors\Dto\AuthorFieldsDto;

/**
 * @psalm-api
 */
final readonly class UpdateAuthorRequestDto
{
    /**
     * @param int $id the author
     * @param AuthorFieldsDto $fields only the fields to change; a list given replaces the whole list, an empty value clears
     */
    public function __construct(
        public int $id,
        public AuthorFieldsDto $fields,
    ) {
    }
}
