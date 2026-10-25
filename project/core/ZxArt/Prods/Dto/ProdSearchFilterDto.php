<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Prods\LegalStatus;
use ZxArt\Releases\ReleaseTypes;

/**
 * What a production search narrows by; every filter given must hold. A list
 * holds when any of its values does.
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
     * @param int[] $countryIds of an author or a group of the production
     * @param int[] $tagIds
     */
    public function __construct(
        public ?string $title = null,
        public array $categoryIds = [],
        public ?int $yearFrom = null,
        public ?int $yearTo = null,
        public array $hardware = [],
        public array $languages = [],
        public array $legalStatuses = [],
        public array $releaseTypes = [],
        public array $formats = [],
        public ?int $authorId = null,
        public ?int $groupId = null,
        public array $countryIds = [],
        public array $tagIds = [],
    ) {
    }
}
