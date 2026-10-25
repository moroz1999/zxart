<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

/**
 * Editable fields of a group. `null` leaves a field as it is, so the same DTO
 * describes a whole form submission and a change of a single field. An empty
 * string or list clears the field, 0 clears a reference or a date; dates are
 * timestamps.
 */
final readonly class GroupFieldsDto
{
    /**
     * @param int[]|null $subGroupIds the groups that belong to this one
     */
    public function __construct(
        public ?string $title = null,
        public ?string $abbreviation = null,
        public ?string $type = null,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?string $website = null,
        public ?string $wikiLink = null,
        public ?string $slogan = null,
        public ?int $startDate = null,
        public ?int $endDate = null,
        public ?array $subGroupIds = null,
    ) {
    }
}
