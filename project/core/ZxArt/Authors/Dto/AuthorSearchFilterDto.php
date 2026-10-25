<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\ProdLink;

/**
 * What an author search narrows by; every filter given must hold, a list when
 * any of its values does. What an alias is credited with, published or is a
 * member of counts for its author. An author is linked to a production it is
 * credited on (made) or published.
 *
 * @psalm-api
 */
final readonly class AuthorSearchFilterDto
{
    /**
     * @param int[] $countryIds
     * @param int[] $cityIds
     * @param int[] $groupIds a member of the group or one of its aliases
     * @param int[] $prodIds linked to one of these productions, as `prodLink` narrows it
     * @param string[] $prodRoles the roles of the credit on the productions given or found, or on any when none
     *     are; roles leave out the publishers, who hold none
     */
    public function __construct(
        /** a part of the nickname, of the real name in any language or of an alias */
        public ?string $name = null,
        public array $countryIds = [],
        public array $cityIds = [],
        public array $groupIds = [],
        /** linked to a production of this year or later */
        public ?int $activeFrom = null,
        /** linked to a production of this year or earlier */
        public ?int $activeTo = null,
        public array $prodIds = [],
        public array $prodRoles = [],
        /** how the author is linked to the productions given or found; alone, authors linked so to any */
        public ?ProdLink $prodLink = null,
        /** linked to a production the nested search finds */
        public ?ProdSearchFilterDto $prods = null,
        /** credited on a production together with this author */
        public ?int $coAuthorId = null,
    ) {
    }
}
