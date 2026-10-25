<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

/**
 * Editable fields of an author. `null` leaves a field as it is, so the same DTO
 * describes a whole form submission and a change of a single field. An empty
 * string clears the field, and 0 clears a reference.
 *
 * The music fields (chip, channels, frequencies) and the palette are the
 * defaults the author's tunes and pictures are converted with.
 */
final readonly class AuthorFieldsDto
{
    public function __construct(
        public ?string $title = null,
        public ?string $realName = null,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?string $wikiLink = null,
        public ?bool $denyVoting = null,
        public ?bool $denyComments = null,
        public ?bool $deny3a = null,
        public ?bool $displayInMusic = null,
        public ?bool $displayInGraphics = null,
        public ?string $chipType = null,
        public ?string $channelsType = null,
        public ?string $frequency = null,
        public ?string $intFrequency = null,
        public ?string $palette = null,
        public ?int $zxTunesId = null,
    ) {
    }
}
