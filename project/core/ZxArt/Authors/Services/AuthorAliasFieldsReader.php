<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Shared\Elements\ElementFields;

/**
 * Reads the editable fields of an author alias into the DTO
 * {@see AuthorAliasFieldsWriter} writes: every field set, an author or a date
 * not set as 0. Checks no privilege.
 *
 * @psalm-api
 */
final readonly class AuthorAliasFieldsReader
{
    public function read(authorAliasElement $alias): AuthorAliasFieldsDto
    {
        return new AuthorAliasFieldsDto(
            title: ElementFields::text($alias, 'title'),
            authorId: ElementFields::number($alias, 'authorId'),
            startDate: ElementFields::timestamp($alias, 'startDate'),
            endDate: ElementFields::timestamp($alias, 'endDate'),
            displayInMusic: ElementFields::flag($alias, 'displayInMusic'),
            displayInGraphics: ElementFields::flag($alias, 'displayInGraphics'),
        );
    }
}
