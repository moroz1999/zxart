<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Shared\Elements\ElementFields;
use zxReleaseElement;

/**
 * Reads the editable fields of a release into the DTO
 * {@see ReleaseFieldsWriter} writes: every field set, a list or a reference
 * left empty as `[]` or 0. Checks no privilege.
 *
 * @psalm-api
 */
final readonly class ReleaseFieldsReader
{
    public function read(zxReleaseElement $release): ReleaseFieldsDto
    {
        return new ReleaseFieldsDto(
            title: ElementFields::text($release, 'title'),
            year: ElementFields::number($release, 'year'),
            version: ElementFields::text($release, 'version'),
            description: ElementFields::text($release, 'description'),
            releaseType: ReleaseTypes::tryFrom(ElementFields::text($release, 'releaseType')),
            releaseFormats: ElementFields::strings($release, 'releaseFormat'),
            languages: ElementFields::strings($release, 'language'),
            hardware: ElementFields::strings($release, 'hardwareRequired'),
            publisherIds: ElementFields::ids($release, 'publishers'),
            prodId: ElementFields::ids($release, 'zxProd')[0] ?? 0,
            denyVoting: ElementFields::flag($release, 'denyVoting'),
            denyComments: ElementFields::flag($release, 'denyComments'),
        );
    }
}
