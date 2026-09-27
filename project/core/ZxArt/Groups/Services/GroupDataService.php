<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupElement;
use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Shared\EntityType;
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
    private const string DELETE_MEMBER_PRIVILEGE = 'deleteAuthor';
    private const string CONVERT_TO_AUTHOR_PRIVILEGE = 'convertToAuthor';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
        private AuthorsService $authorsService,
        private AuthorshipRepository $authorshipRepository,
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

    /**
     * Removes the author from the group's members.
     */
    public function deleteMember(int $groupId, int $authorId): GroupDataResultDto
    {
        $group = $this->getGroup($groupId, self::DELETE_MEMBER_PRIVILEGE);
        if ($authorId <= 0) {
            throw new GroupDataException('Missing required author id', 400);
        }
        $isDeleted = $this->authorshipRepository->deleteAuthorship($groupId, $authorId, EntityType::Group);
        if (!$isDeleted) {
            throw new GroupDataException('The author is not a member of this group', 404);
        }
        $this->actionsLogService->log($group, StructureType::Group, self::DELETE_MEMBER_PRIVILEGE);

        return new GroupDataResultDto(id: $groupId);
    }

    private function getGroup(int $groupId, string $privilege): groupElement
    {
        $group = $this->findGroup($groupId);
        $this->assertAllowed($groupId, $privilege, StructureType::Group);

        return $group;
    }

    private function findGroup(int $groupId): groupElement
    {
        if ($groupId <= 0) {
            throw new GroupDataException('Missing required group id', 400);
        }
        $group = $this->structureManager->getElementById($groupId);
        if (!$group instanceof groupElement) {
            throw new GroupDataException('Group not found', 404);
        }

        return $group;
    }

    private function assertAllowed(int $elementId, string $privilege, StructureType $structureType): void
    {
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            $privilege,
            $structureType->value,
        ) === true;
        if (!$isAllowed) {
            throw new GroupDataException('This group change is forbidden', 403);
        }
    }
}
