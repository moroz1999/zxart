<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use privilegesManager;
use structureManager;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Shared\StructureType;

/**
 * The privilege checks of the author use cases: the privilege of the
 * matching legacy action, on the element it is held on.
 *
 * @psalm-api
 */
final readonly class AuthorPrivileges
{
    /** Held on the root: adding an author anywhere in the catalogue. */
    private const string CREATE_PRIVILEGE = 'publicAdd';

    public function __construct(
        private privilegesManager $privilegesManager,
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws AuthorDataException 403
     */
    public function assert(int $elementId, string $privilege, StructureType $structureType = StructureType::Author): void
    {
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            $privilege,
            $structureType->value,
        ) === true;
        if (!$isAllowed) {
            throw new AuthorDataException('This author change is forbidden', 403);
        }
    }

    /**
     * Adding an author anywhere in the catalogue: the privilege is held on the
     * root, whose privileges are worked out as it loads.
     *
     * @throws AuthorDataException 403
     */
    public function assertMayCreate(): void
    {
        $this->structureManager->getRootElement();
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $this->structureManager->getRootElementId(),
            self::CREATE_PRIVILEGE,
            StructureType::Author->value,
        ) === true;
        if (!$isAllowed) {
            throw new AuthorDataException('Adding this author is forbidden', 403);
        }
    }
}
