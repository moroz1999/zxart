<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Shared\Elements\ElementFields;

/**
 * Reads the editable fields of an author into the DTO
 * {@see AuthorFieldsWriter} writes: every field set, a reference left empty
 * as 0. Checks no privilege.
 *
 * @psalm-api
 */
final readonly class AuthorFieldsReader
{
    public function read(authorElement $author): AuthorFieldsDto
    {
        return new AuthorFieldsDto(
            title: ElementFields::text($author, 'title'),
            realName: ElementFields::text($author, 'realName'),
            countryId: ElementFields::number($author, 'country'),
            cityId: ElementFields::number($author, 'city'),
            wikiLink: ElementFields::text($author, 'wikiLink'),
            denyVoting: ElementFields::flag($author, 'denyVoting'),
            denyComments: ElementFields::flag($author, 'denyComments'),
            deny3a: ElementFields::flag($author, 'deny3a'),
            displayInMusic: ElementFields::flag($author, 'displayInMusic'),
            displayInGraphics: ElementFields::flag($author, 'displayInGraphics'),
            chipType: ElementFields::text($author, 'chipType'),
            channelsType: ElementFields::text($author, 'channelsType'),
            frequency: ElementFields::text($author, 'frequency'),
            intFrequency: ElementFields::text($author, 'intFrequency'),
            palette: ElementFields::text($author, 'palette'),
            zxTunesId: ElementFields::number($author, 'zxTunesId'),
        );
    }
}
