<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupElement;
use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Shared\StructureType;

/**
 * Changes to a group requested through `/group-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class GroupDataService
{
    private const string DELETE_PRIVILEGE = 'publicDelete';
    private const string CONVERT_TO_AUTHOR_PRIVILEGE = 'convertToAuthor';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
        private AuthorsService $authorsService,
    ) {
    }

    public function delete(int $groupId): GroupDataResultDto
    {
        $group = $this->getGroup($groupId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($group, StructureType::Group, self::DELETE_PRIVILEGE);
        $group->deleteElementData();

        return new GroupDataResultDto(id: $groupId);
    }

    /**
     * Turns the group into a new author; the group itself is deleted.
     */
    public function convertToAuthor(int $groupId): GroupDataResultDto
    {
        $group = $this->getGroup($groupId, self::CONVERT_TO_AUTHOR_PRIVILEGE);
        $author = $this->authorsService->convertGroupToAuthor($group);
        if ($author === null) {
            throw new GroupDataException('The author could not be created', 500);
        }
        $this->actionsLogService->log($group, StructureType::Group, self::CONVERT_TO_AUTHOR_PRIVILEGE);

        return new GroupDataResultDto(id: $author->getId());
    }

    private function getGroup(int $groupId, string $privilege): groupElement
    {
        if ($groupId <= 0) {
            throw new GroupDataException('Missing required group id', 400);
        }
        $group = $this->structureManager->getElementById($groupId);
        if (!$group instanceof groupElement) {
            throw new GroupDataException('Group not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $groupId,
            $privilege,
            StructureType::Group->value,
        ) === true;
        if (!$isAllowed) {
            throw new GroupDataException('This group change is forbidden', 403);
        }

        return $group;
    }
}
