<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use ZxArt\Import\Dto\ImportOriginDto;

/**
 * The JSON part of an author create or update request. A list left out keeps
 * what the author holds; an empty list clears it.
 */
final readonly class AuthorSaveRequestDto
{
    /**
     * @param array<int, string>|null $realNames language id => the real name in it
     * @param list<MembershipInputDto>|null $groups
     * @param list<MembershipInputDto>|null $prods
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public AuthorFieldsDto $fields = new AuthorFieldsDto(),
        public ?array $realNames = null,
        public ?array $groups = null,
        public ?array $prods = null,
        public ?array $importOrigins = null,
        public bool $removeImage = false,
    ) {
    }
}
