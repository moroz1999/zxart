<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use ZxArt\Groups\Dto\FoundGroupDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityRefs;

/**
 * The summaries of the groups a search found, in the order of the ids.
 *
 * @psalm-api
 */
final readonly class FoundGroupsReader
{
    public function __construct(
        private GroupRepository $groupRepository,
    ) {
    }

    /**
     * @param int[] $groupIds
     * @return list<FoundGroupDto>
     * @throws GroupDataException for an id of no group
     */
    public function read(array $groupIds): array
    {
        $found = [];
        foreach ($groupIds as $groupId) {
            $group = $this->groupRepository->get($groupId);
            $country = $group->getCountryElement();
            $city = $group->getCityElement();
            $found[] = new FoundGroupDto(
                id: $groupId,
                title: html_entity_decode(ElementFields::text($group, 'title'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                abbreviation: html_entity_decode(ElementFields::text($group, 'abbreviation'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                type: ElementFields::text($group, 'type'),
                country: $country !== null ? EntityRefs::of($country) : null,
                city: $city !== null ? EntityRefs::of($city) : null,
                aliases: EntityRefs::list((array)$group->getAliasElements()),
            );
        }

        return $found;
    }
}
