<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use privilegesManager;
use structureManager;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Shared\StructureType;

/**
 * The privilege checks of the group use cases: the privilege of the
 * matching legacy action, on the element it is held on.
 *
 * @psalm-api
 */
final readonly class GroupPrivileges
{
    /** Held on the root: adding a group anywhere in the catalogue. */
    private const string CREATE_PRIVILEGE = 'publicAdd';

    public function __construct(
        private privilegesManager $privilegesManager,
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws GroupDataException 403
     */
    public function assert(int $elementId, string $privilege, StructureType $structureType = StructureType::Group): void
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

    /**
     * Adding a group anywhere in the catalogue: the privilege is held on the
     * root, whose privileges are worked out as it loads.
     *
     * @throws GroupDataException 403
     */
    public function assertMayCreate(): void
    {
        $this->structureManager->getRootElement();
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $this->structureManager->getRootElementId(),
            self::CREATE_PRIVILEGE,
            StructureType::Group->value,
        ) === true;
        if (!$isAllowed) {
            throw new GroupDataException('Adding this group is forbidden', 403);
        }
    }
}
