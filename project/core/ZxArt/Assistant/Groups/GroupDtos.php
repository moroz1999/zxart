<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Authors\Services\MembershipsReader;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupDetailsService;
use ZxArt\LinkTypes;
use ZxArt\Shared\Dbal\StructureLinksDbal;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * Builds the assistant's group and group alias DTOs from the stored elements;
 * the groups a group is a subgroup of are read from the links. Checks no
 * privilege.
 *
 * @psalm-api
 */
final readonly class GroupDtos
{
    public function __construct(
        private GroupRepository $groupRepository,
        private GroupAliasRepository $groupAliasRepository,
        private MembershipsReader $membershipsReader,
        private GroupDetailsService $groupDetailsService,
        private EntityRefsReader $entityRefsReader,
        private StructureLinksDbal $structureLinksDbal,
    ) {
    }

    /**
     * @throws GroupDataException for an id of no group
     */
    public function group(int $groupId): GroupDto
    {
        $group = $this->groupRepository->get($groupId);
        $countryId = ElementFields::number($group, 'country');
        $cityId = ElementFields::number($group, 'city');
        /** @var int[] $storedSubGroupIds */
        $storedSubGroupIds = $group->getSubGroupIds();
        $subGroupIds = array_values(array_map('intval', $storedSubGroupIds));

        return new GroupDto(
            id: $groupId,
            title: ElementFields::text($group, 'title'),
            abbreviation: ElementFields::text($group, 'abbreviation'),
            type: ElementFields::text($group, 'type'),
            countryId: $countryId,
            cityId: $cityId,
            website: ElementFields::text($group, 'website'),
            wikiLink: ElementFields::text($group, 'wikiLink'),
            slogan: ElementFields::text($group, 'slogan'),
            startDate: ElementFields::timestamp($group, 'startDate'),
            endDate: ElementFields::timestamp($group, 'endDate'),
            subGroupIds: $subGroupIds,
            refs: $this->entityRefsReader->read([$countryId, $cityId, ...$subGroupIds]),
            aliases: $this->membershipsReader->aliases($group->getAliasElements()),
            members: $this->membershipsReader->membersOf($groupId),
            parentGroups: $this->entityRefsReader->read($this->structureLinksDbal->parentIds($groupId, LinkTypes::GROUP_SUBGROUP)),
            counters: $this->groupDetailsService->getCounters($group),
        );
    }

    /**
     * @throws GroupAliasDataException for an id of no group alias
     */
    public function alias(int $aliasId): GroupAliasDto
    {
        $alias = $this->groupAliasRepository->get($aliasId);
        $groupId = ElementFields::number($alias, 'groupId');

        return new GroupAliasDto(
            id: $aliasId,
            title: ElementFields::text($alias, 'title'),
            groupId: $groupId,
            startDate: ElementFields::timestamp($alias, 'startDate'),
            endDate: ElementFields::timestamp($alias, 'endDate'),
            refs: $this->entityRefsReader->read([$groupId]),
            members: $this->membershipsReader->membersOf($aliasId),
            counters: $this->groupDetailsService->getCounters($alias),
        );
    }
}
