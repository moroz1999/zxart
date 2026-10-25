<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupElement;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Shared\Elements\ElementFields;

/**
 * Reads the editable fields of a group into the DTO {@see GroupFieldsWriter}
 * writes: every field set, a reference or a date not set as 0. Sub-groups are
 * read from their links. Checks no privilege.
 *
 * @psalm-api
 */
final readonly class GroupFieldsReader
{
    public function read(groupElement $group): GroupFieldsDto
    {
        /** @var int[] $subGroupIds */
        $subGroupIds = $group->getSubGroupIds();

        return new GroupFieldsDto(
            title: ElementFields::text($group, 'title'),
            abbreviation: ElementFields::text($group, 'abbreviation'),
            type: ElementFields::text($group, 'type'),
            countryId: ElementFields::number($group, 'country'),
            cityId: ElementFields::number($group, 'city'),
            website: ElementFields::text($group, 'website'),
            wikiLink: ElementFields::text($group, 'wikiLink'),
            slogan: ElementFields::text($group, 'slogan'),
            startDate: ElementFields::timestamp($group, 'startDate'),
            endDate: ElementFields::timestamp($group, 'endDate'),
            subGroupIds: array_values(array_map('intval', $subGroupIds)),
        );
    }
}
