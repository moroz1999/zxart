<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Prods\LegalStatus;

/**
 * Editable fields of a production. `null` leaves a field as it is, so the same
 * DTO describes a whole form submission and a change of a single field. An
 * empty string or list clears the field.
 */
final readonly class ProdFieldsDto
{
    /**
     * @param string[]|null $languages language codes
     * @param string[]|null $hardware hardware codes
     * @param int[]|null $categoryIds
     * @param int[]|null $groupIds groups and group aliases
     * @param int[]|null $publisherIds groups, group aliases, authors and author aliases
     * @param string[]|null $tags tag titles
     * @param int[]|null $compilationItemIds productions and releases
     * @param int[]|null $seriesProdIds
     */
    public function __construct(
        public ?string $title = null,
        public ?string $altTitle = null,
        public ?int $year = null,
        public ?string $description = null,
        public ?bool $htmlDescription = null,
        public ?string $instructions = null,
        public ?bool $htmlInstructions = null,
        public ?LegalStatus $legalStatus = null,
        public ?string $externalLink = null,
        public ?string $youtubeId = null,
        public ?int $partyId = null,
        public ?int $partyPlace = null,
        public ?string $compo = null,
        public ?bool $denyVoting = null,
        public ?bool $denyComments = null,
        public ?array $languages = null,
        public ?array $hardware = null,
        public ?array $categoryIds = null,
        public ?array $groupIds = null,
        public ?array $publisherIds = null,
        public ?array $tags = null,
        public ?array $compilationItemIds = null,
        public ?array $seriesProdIds = null,
    ) {
    }
}
