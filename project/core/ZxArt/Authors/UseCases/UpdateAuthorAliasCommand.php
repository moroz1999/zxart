<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\MembershipInputDto;
use ZxArt\Import\Dto\ImportOriginDto;

/**
 * A list left `null` keeps what the alias holds; an empty list clears it.
 */
final readonly class UpdateAuthorAliasCommand
{
    /**
     * @param list<MembershipInputDto>|null $groups
     * @param list<MembershipInputDto>|null $prods
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public int $aliasId,
        public AuthorAliasFieldsDto $fields,
        public ?array $groups = null,
        public ?array $prods = null,
        public ?array $importOrigins = null,
    ) {
    }
}
