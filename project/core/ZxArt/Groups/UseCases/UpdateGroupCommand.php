<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * A list left `null` keeps what the group holds; an empty list clears it. A
 * new image replaces the stored one; `removeImage` drops it.
 */
final readonly class UpdateGroupCommand
{
    /**
     * @param list<AuthorshipInputDto>|null $members
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public int $groupId,
        public GroupFieldsDto $fields,
        public ?array $members = null,
        public ?array $importOrigins = null,
        public ?UploadedFileDto $image = null,
        public bool $removeImage = false,
    ) {
    }
}
