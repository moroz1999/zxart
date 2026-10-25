<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupAliasElement;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Shared\Elements\ElementFields;

/**
 * Reads the editable fields of a group alias into the DTO
 * {@see GroupAliasFieldsWriter} writes: every field set, a group or a date not
 * set as 0. Checks no privilege.
 *
 * @psalm-api
 */
final readonly class GroupAliasFieldsReader
{
    public function read(groupAliasElement $alias): GroupAliasFieldsDto
    {
        return new GroupAliasFieldsDto(
            title: ElementFields::text($alias, 'title'),
            groupId: ElementFields::number($alias, 'groupId'),
            startDate: ElementFields::timestamp($alias, 'startDate'),
            endDate: ElementFields::timestamp($alias, 'endDate'),
        );
    }
}
