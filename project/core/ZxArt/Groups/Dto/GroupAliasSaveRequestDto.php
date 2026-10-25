<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Import\Dto\ImportOriginDto;

/**
 * The JSON part of a group alias create or update request. A list left out
 * keeps what the alias holds; an empty list clears it.
 */
final readonly class GroupAliasSaveRequestDto
{
    /**
     * @param list<AuthorshipInputDto>|null $members
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public GroupAliasFieldsDto $fields = new GroupAliasFieldsDto(),
        public ?array $members = null,
        public ?array $importOrigins = null,
    ) {
    }
}
