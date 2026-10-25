<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use ZxArt\Import\Dto\ImportOriginDto;

/**
 * The JSON part of an author alias create or update request. A list left out
 * keeps what the alias holds; an empty list clears it.
 */
final readonly class AuthorAliasSaveRequestDto
{
    /**
     * @param list<MembershipInputDto>|null $groups
     * @param list<MembershipInputDto>|null $prods
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public AuthorAliasFieldsDto $fields = new AuthorAliasFieldsDto(),
        public ?array $groups = null,
        public ?array $prods = null,
        public ?array $importOrigins = null,
    ) {
    }
}
