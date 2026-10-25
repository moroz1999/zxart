<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\MembershipInputDto;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * A list left `null` stores nothing; an empty list is the same.
 */
final readonly class CreateAuthorCommand
{
    /**
     * @param array<int, string>|null $realNames language id => the real name in it
     * @param list<MembershipInputDto>|null $groups
     * @param list<MembershipInputDto>|null $prods
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public AuthorFieldsDto $fields,
        public ?array $realNames = null,
        public ?array $groups = null,
        public ?array $prods = null,
        public ?array $importOrigins = null,
        public ?UploadedFileDto $image = null,
    ) {
    }
}
