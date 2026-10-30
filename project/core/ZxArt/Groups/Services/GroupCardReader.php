<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use ZxArt\Authors\Services\MembershipsReader;
use ZxArt\Groups\Dto\GroupCardDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\LinkTypes;
use ZxArt\Shared\Dbal\StructureLinksDbal;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * @psalm-api
 */
final readonly class GroupCardReader
{
    public function __construct(
        private GroupRepository $groupRepository,
        private GroupFieldsReader $groupFieldsReader,
        private MembershipsReader $membershipsReader,
        private GroupDetailsService $groupDetailsService,
        private EntityRefsReader $entityRefsReader,
        private StructureLinksDbal $structureLinksDbal,
    ) {
    }

    /**
     * @throws GroupDataException for an id of no group
     */
    public function read(int $groupId): GroupCardDto
    {
        $group = $this->groupRepository->get($groupId);
        $fields = $this->groupFieldsReader->read($group);

        return new GroupCardDto(
            id: $groupId,
            fields: $fields,
            refs: $this->entityRefsReader->read([$fields->countryId ?? 0, $fields->cityId ?? 0, ...($fields->subGroupIds ?? [])]),
            members: $this->membershipsReader->membersOf($groupId),
            parentGroups: $this->entityRefsReader->read($this->structureLinksDbal->parentIds($groupId, LinkTypes::GROUP_SUBGROUP)),
            aliases: $this->membershipsReader->aliases((array)$group->getAliasElements()),
            counters: $this->groupDetailsService->getCounters($group),
        );
    }
}
