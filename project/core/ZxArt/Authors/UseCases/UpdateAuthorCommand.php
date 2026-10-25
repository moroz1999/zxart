<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\Authors\Dto\AuthorFieldsDto;

final readonly class UpdateAuthorCommand
{
    /**
     * @param AuthorFieldsDto $fields only the fields it carries change
     */
    public function __construct(
        public int $authorId,
        public AuthorFieldsDto $fields,
    ) {
    }
}
