<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorAliasElement;
use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Shared\StructureType;

/**
 * Changes to an author alias requested through `/author-alias-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class AuthorAliasDataService
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

    public function delete(int $aliasId): AuthorAliasDataResultDto
    {
        $alias = $this->getAlias($aliasId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($alias, StructureType::AuthorAlias, self::DELETE_PRIVILEGE);
        $alias->deleteElementData();

        return new AuthorAliasDataResultDto(id: $aliasId);
    }

    /**
     * Turns the author alias into a new author; the author alias itself is deleted.
     */
    public function convertToAuthor(int $aliasId): AuthorAliasDataResultDto
    {
        $alias = $this->getAlias($aliasId, self::CONVERT_TO_AUTHOR_PRIVILEGE);
        $author = $this->authorsService->convertAliasToAuthor($alias);
        if ($author === null) {
            throw new AuthorAliasDataException('The author could not be created', 500);
        }
        $this->actionsLogService->log($alias, StructureType::AuthorAlias, self::CONVERT_TO_AUTHOR_PRIVILEGE);

        return new AuthorAliasDataResultDto(id: $author->getId());
    }

    private function getAlias(int $aliasId, string $privilege): authorAliasElement
    {
        if ($aliasId <= 0) {
            throw new AuthorAliasDataException('Missing required author alias id', 400);
        }
        $alias = $this->structureManager->getElementById($aliasId);
        if (!$alias instanceof authorAliasElement) {
            throw new AuthorAliasDataException('Author alias not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $aliasId,
            $privilege,
            StructureType::AuthorAlias->value,
        ) === true;
        if (!$isAllowed) {
            throw new AuthorAliasDataException('This author alias change is forbidden', 403);
        }

        return $alias;
    }
}
