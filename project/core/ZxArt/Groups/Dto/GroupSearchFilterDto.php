<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\ProdLink;

/**
 * What a group search narrows by; every filter given must hold, a list when
 * any of its values does. What an alias made, published or holds as members
 * counts for its group. Dates are Unix timestamps; a group without the date
 * does not match its range.
 *
 * @psalm-api
 */
final readonly class GroupSearchFilterDto
{
    /**
     * @param int[] $countryIds
     * @param int[] $cityIds
     * @param string[] $types
     * @param int[] $memberIds authors (or their aliases) recorded as members
     * @param int[] $prodIds made or published one of these productions, as `prodLink` narrows it
     */
    public function __construct(
        /** a part of the title, of the abbreviation or of an alias */
        public ?string $name = null,
        public array $countryIds = [],
        public array $cityIds = [],
        public array $types = [],
        public ?int $foundedFrom = null,
        public ?int $foundedTo = null,
        public ?int $endedFrom = null,
        public ?int $endedTo = null,
        /** made or published a production of this year or later */
        public ?int $activeFrom = null,
        /** made or published a production of this year or earlier */
        public ?int $activeTo = null,
        public array $memberIds = [],
        public array $prodIds = [],
        /** what the group did for the productions given or found; alone, groups that did it for any */
        public ?ProdLink $prodLink = null,
        /** made or published a production the nested search finds */
        public ?ProdSearchFilterDto $prods = null,
        /** a subgroup of this group */
        public ?int $parentGroupId = null,
        /** a group this group is a subgroup of */
        public ?int $subGroupId = null,
    ) {
    }
}
