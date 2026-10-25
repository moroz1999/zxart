<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupElement;
use linksManager;
use structureManager;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Groups\Dto\GroupStateDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Import\Services\ImportOriginsReader;
use ZxArt\LinkTypes;
use ZxArt\Shared\EntityType;

/**
 * Reads the complete state of a group. Checks no privilege beyond what loading
 * the element checks.
 *
 * @psalm-api
 */
final readonly class GroupStateService
{
    public function __construct(
        private structureManager $structureManager,
        private GroupFieldsReader $groupFieldsReader,
        private AuthorshipRepository $authorshipRepository,
        private GroupDetailsService $groupDetailsService,
        private linksManager $linksManager,
        private ImportOriginsReader $importOriginsReader,
    ) {
    }

    public function get(int $groupId): GroupStateDto
    {
        $group = $this->structureManager->getElementById($groupId);
        if (!$group instanceof groupElement) {
            throw new GroupDataException('Group not found', 404);
        }

        // read from the links: the element's own list keeps what it loaded first
        /** @var int[] $parentGroupIds */
        $parentGroupIds = $this->linksManager->getConnectedIdList($groupId, LinkTypes::GROUP_SUBGROUP->value, 'child');

        return new GroupStateDto(
            id: $groupId,
            modifiedAt: $group->getModifiedTimestamp(),
            fields: $this->groupFieldsReader->read($group),
            members: $this->authorshipRepository->findByElement($groupId, EntityType::Group),
            parentGroupIds: array_values(array_map('intval', $parentGroupIds)),
            aliasIds: array_values(array_map('intval', $group->getAliasElementsIds())),
            counters: $this->groupDetailsService->getCounters($group),
            importOrigins: $this->importOriginsReader->read($groupId),
        );
    }
}
