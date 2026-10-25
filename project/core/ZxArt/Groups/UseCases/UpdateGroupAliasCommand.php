<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Import\Dto\ImportOriginDto;

/**
 * A list left `null` keeps what the alias holds; an empty list clears it.
 */
final readonly class UpdateGroupAliasCommand
{
    /**
     * @param list<AuthorshipInputDto>|null $members
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public int $aliasId,
        public GroupAliasFieldsDto $fields,
        public ?array $members = null,
        public ?array $importOrigins = null,
    ) {
    }
}
