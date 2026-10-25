<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use ZxArt\Releases\ReleaseTypes;

/**
 * Editable fields of a release. `null` leaves a field as it is, so the same DTO
 * describes a whole form submission and a change of a single field. An empty
 * string or list clears the field. The release file is not one of them: it is
 * uploaded, not written.
 */
final readonly class ReleaseFieldsDto
{
    /**
     * @param string[]|null $releaseFormats release format codes
     * @param string[]|null $languages language codes
     * @param string[]|null $hardware hardware codes the release needs beyond its production's
     * @param int[]|null $publisherIds groups, group aliases, authors and author aliases
     * @param int|null $prodId the production the release belongs to; another one moves it
     */
    public function __construct(
        public ?string $title = null,
        public ?int $year = null,
        public ?string $version = null,
        public ?string $description = null,
        public ?ReleaseTypes $releaseType = null,
        public ?array $releaseFormats = null,
        public ?array $languages = null,
        public ?array $hardware = null,
        public ?array $publisherIds = null,
        public ?int $prodId = null,
        public ?bool $denyVoting = null,
        public ?bool $denyComments = null,
    ) {
    }
}
