<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Import\Dto\ImportOriginDto;

/**
 * A list left `null` stores nothing; an empty list is the same.
 */
final readonly class CreateGroupAliasCommand
{
    /**
     * @param list<AuthorshipInputDto>|null $members
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public GroupAliasFieldsDto $fields,
        public ?array $members = null,
        public ?array $importOrigins = null,
    ) {
    }
}
