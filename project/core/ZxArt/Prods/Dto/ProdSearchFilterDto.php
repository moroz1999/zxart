<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Prods\ProdLink;
use ZxArt\Prods\LegalStatus;
use ZxArt\Releases\ReleaseTypes;

/**
 * What a production search narrows by; every filter given must hold. A list
 * holds when any of its values does, except the authors: all of them must be
 * credited unless `anyAuthor` is set. An author or a group takes in its
 * aliases. Dates are Unix timestamps.
 *
 * @psalm-api
 */
final readonly class ProdSearchFilterDto
{
    /**
     * @param int[] $categoryIds a category takes in its subcategories
     * @param string[] $hardware hardware codes, on the production or a release
     * @param string[] $languages language codes, on the production or a release
     * @param LegalStatus[] $legalStatuses
     * @param ReleaseTypes[] $releaseTypes of any release
     * @param string[] $formats of any release
     * @param int[] $authorIds credited on the production itself
     * @param string[] $authorRoles the roles the authors, or anyone when no author is given, are credited with
     * @param int[] $groupIds that made or published the production, as `groupLink` narrows it
     * @param int[] $publisherIds authors or groups that published the production
     * @param int[] $countryIds of an author or a developer group of the production
     * @param int[] $cityIds of an author or a developer group of the production
     * @param int[] $partyIds where the production was released
     * @param int[] $tagIds
     */
    public function __construct(
        public ?string $title = null,
        public array $categoryIds = [],
        public ?int $yearFrom = null,
        public ?int $yearTo = null,
        public ?int $releaseYearFrom = null,
        public ?int $releaseYearTo = null,
        public ?int $addedFrom = null,
        public ?int $addedTo = null,
        public array $hardware = [],
        public array $languages = [],
        public array $legalStatuses = [],
        public array $releaseTypes = [],
        public array $formats = [],
        public array $authorIds = [],
        public bool $anyAuthor = false,
        public array $authorRoles = [],
        public array $groupIds = [],
        public ?ProdLink $groupLink = null,
        public array $publisherIds = [],
        public array $countryIds = [],
        public array $cityIds = [],
        public array $partyIds = [],
        public ?string $compo = null,
        public ?int $maxPartyPlace = null,
        /** the series production whose parts are searched */
        public ?int $seriesId = null,
        /** the compilation whose items are searched */
        public ?int $compilationId = null,
        /** a production the searched compilations include */
        public ?int $includedProdId = null,
        public array $tagIds = [],
    ) {
    }
}
