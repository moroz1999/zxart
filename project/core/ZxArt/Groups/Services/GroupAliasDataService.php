<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupAliasElement;
use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Shared\StructureType;

/**
 * Changes to a group alias requested through `/group-alias-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class GroupAliasDataService
{
    private const string DELETE_PRIVILEGE = 'publicDelete';
    private const string CONVERT_TO_GROUP_PRIVILEGE = 'convertToGroup';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
        private GroupsService $groupsService,
    ) {
    }

    public function delete(int $aliasId): GroupAliasDataResultDto
    {
        $alias = $this->getAlias($aliasId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($alias, StructureType::GroupAlias, self::DELETE_PRIVILEGE);
        $alias->deleteElementData();

        return new GroupAliasDataResultDto(id: $aliasId);
    }

    /**
     * Turns the group alias into a new group; the group alias itself is deleted.
     */
    public function convertToGroup(int $aliasId): GroupAliasDataResultDto
    {
        $alias = $this->getAlias($aliasId, self::CONVERT_TO_GROUP_PRIVILEGE);
        $group = $this->groupsService->convertGroupAliasToGroup($alias);
        if ($group === null) {
            throw new GroupAliasDataException('The group could not be created', 500);
        }
        $this->actionsLogService->log($alias, StructureType::GroupAlias, self::CONVERT_TO_GROUP_PRIVILEGE);

        return new GroupAliasDataResultDto(id: $group->getId());
    }

    private function getAlias(int $aliasId, string $privilege): groupAliasElement
    {
        if ($aliasId <= 0) {
            throw new GroupAliasDataException('Missing required group alias id', 400);
        }
        $alias = $this->structureManager->getElementById($aliasId);
        if (!$alias instanceof groupAliasElement) {
            throw new GroupAliasDataException('Group alias not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $aliasId,
            $privilege,
            StructureType::GroupAlias->value,
        ) === true;
        if (!$isAllowed) {
            throw new GroupAliasDataException('This group alias change is forbidden', 403);
        }

        return $alias;
    }
}
