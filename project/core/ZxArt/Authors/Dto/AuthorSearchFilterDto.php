<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use ZxArt\Prods\Dto\ProdSearchFilterDto;

/**
 * What an author search narrows by; every filter given must hold, a list when
 * any of its values does. What an alias is credited with or a member of counts
 * for its author.
 *
 * @psalm-api
 */
final readonly class AuthorSearchFilterDto
{
    /**
     * @param int[] $countryIds
     * @param int[] $cityIds
     * @param int[] $groupIds a member of the group or one of its aliases
     * @param int[] $prodIds credited on one of these productions
     * @param string[] $prodRoles the roles of the credit on the productions given or found, or on any when none are
     */
    public function __construct(
        /** a part of the nickname, of the real name in any language or of an alias */
        public ?string $name = null,
        public array $countryIds = [],
        public array $cityIds = [],
        public array $groupIds = [],
        /** credited on a production of this year or later */
        public ?int $activeFrom = null,
        /** credited on a production of this year or earlier */
        public ?int $activeTo = null,
        public array $prodIds = [],
        public array $prodRoles = [],
        /** credited on a production the nested search finds */
        public ?ProdSearchFilterDto $prods = null,
        /** credited on a production together with this author */
        public ?int $coAuthorId = null,
    ) {
    }
}
