<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Shared\StructureType;

/**
 * Changes to an author requested through `/author-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class AuthorDataService
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

    public function delete(int $authorId): AuthorDataResultDto
    {
        $author = $this->getAuthor($authorId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($author, StructureType::Author, self::DELETE_PRIVILEGE);
        $author->deleteElementData();

        return new AuthorDataResultDto(id: $authorId);
    }

    /**
     * Turns the author into a new group; the author itself is deleted.
     */
    public function convertToGroup(int $authorId): AuthorDataResultDto
    {
        $author = $this->getAuthor($authorId, self::CONVERT_TO_GROUP_PRIVILEGE);
        $group = $this->groupsService->convertAuthorToGroup($author);
        if ($group === null) {
            throw new AuthorDataException('The group could not be created', 500);
        }
        $this->actionsLogService->log($author, StructureType::Author, self::CONVERT_TO_GROUP_PRIVILEGE);

        return new AuthorDataResultDto(id: $group->getId());
    }

    private function getAuthor(int $authorId, string $privilege): authorElement
    {
        if ($authorId <= 0) {
            throw new AuthorDataException('Missing required author id', 400);
        }
        $author = $this->structureManager->getElementById($authorId);
        if (!$author instanceof authorElement) {
            throw new AuthorDataException('Author not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $authorId,
            $privilege,
            StructureType::Author->value,
        ) === true;
        if (!$isAllowed) {
            throw new AuthorDataException('This author change is forbidden', 403);
        }

        return $author;
    }
}
