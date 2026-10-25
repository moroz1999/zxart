<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use authorElement;
use privilegesManager;
use structureManager;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Shared\StructureType;

/**
 * The privilege checks of the author alias use cases: the privilege of the
 * matching legacy action, on the element it is held on.
 *
 * @psalm-api
 */
final readonly class AuthorAliasPrivileges
{
    /** Held on the author: adding an alias to it. */
    private const string CREATE_PRIVILEGE = 'publicAdd';

    public function __construct(
        private privilegesManager $privilegesManager,
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws AuthorAliasDataException 403
     */
    public function assert(int $elementId, string $privilege, StructureType $structureType = StructureType::AuthorAlias): void
    {
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            $privilege,
            $structureType->value,
        ) === true;
        if (!$isAllowed) {
            throw new AuthorAliasDataException('This author alias change is forbidden', 403);
        }
    }

    /**
     * Adding an alias to its owner. The owner is loaded first: the privileges
     * of an element are worked out as it loads.
     *
     * @throws AuthorAliasDataException 400 for no owner of the id, 403
     */
    public function assertMayAddTo(int $ownerId): void
    {
        if (!$this->structureManager->getElementById($ownerId) instanceof authorElement) {
            throw new AuthorAliasDataException('The author of an alias must be an author', 400);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $ownerId,
            self::CREATE_PRIVILEGE,
            StructureType::AuthorAlias->value,
        ) === true;
        if (!$isAllowed) {
            throw new AuthorAliasDataException('Adding this author alias is forbidden', 403);
        }
    }
}
