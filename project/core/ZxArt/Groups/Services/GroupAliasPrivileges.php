<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use groupElement;
use privilegesManager;
use structureManager;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Shared\StructureType;

/**
 * The privilege checks of the group alias use cases: the privilege of the
 * matching legacy action, on the element it is held on.
 *
 * @psalm-api
 */
final readonly class GroupAliasPrivileges
{
    /** Held on the group: adding an alias to it. */
    private const string CREATE_PRIVILEGE = 'publicAdd';

    public function __construct(
        private privilegesManager $privilegesManager,
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws GroupAliasDataException 403
     */
    public function assert(int $elementId, string $privilege, StructureType $structureType = StructureType::GroupAlias): void
    {
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            $privilege,
            $structureType->value,
        ) === true;
        if (!$isAllowed) {
            throw new GroupAliasDataException('This group alias change is forbidden', 403);
        }
    }

    /**
     * Adding an alias to its owner. The owner is loaded first: the privileges
     * of an element are worked out as it loads.
     *
     * @throws GroupAliasDataException 400 for no owner of the id, 403
     */
    public function assertMayAddTo(int $ownerId): void
    {
        if (!$this->structureManager->getElementById($ownerId) instanceof groupElement) {
            throw new GroupAliasDataException('The group of an alias must be a group', 400);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $ownerId,
            self::CREATE_PRIVILEGE,
            StructureType::GroupAlias->value,
        ) === true;
        if (!$isAllowed) {
            throw new GroupAliasDataException('Adding this group alias is forbidden', 403);
        }
    }
}
