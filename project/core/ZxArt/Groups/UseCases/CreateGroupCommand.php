<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * A list left `null` stores nothing; an empty list is the same.
 */
final readonly class CreateGroupCommand
{
    /**
     * @param list<AuthorshipInputDto>|null $members
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public GroupFieldsDto $fields,
        public ?array $members = null,
        public ?array $importOrigins = null,
        public ?UploadedFileDto $image = null,
    ) {
    }
}
