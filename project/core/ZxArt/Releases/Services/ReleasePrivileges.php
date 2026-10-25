<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use privilegesManager;
use structureManager;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Shared\StructureType;
use zxProdElement;

/**
 * The privilege check of the release use cases: the privilege of the matching
 * legacy action, on the element it is held on.
 *
 * @psalm-api
 */
final readonly class ReleasePrivileges
{
    /** Held on the production: adding a release to it. */
    private const string ADD_PRIVILEGE = 'publicAdd';

    public function __construct(
        private privilegesManager $privilegesManager,
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws ReleaseDataException 403
     */
    public function assert(int $elementId, string $privilege, StructureType $structureType = StructureType::ZxRelease): void
    {
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            $privilege,
            $structureType->value,
        ) === true;
        if (!$isAllowed) {
            throw new ReleaseDataException('This release change is forbidden', 403);
        }
    }

    /**
     * Adding a release to a production, or moving one there. The production is
     * loaded first: the privileges of an element are worked out as it loads.
     *
     * @throws ReleaseDataException 400 for no production of the id, 403
     */
    public function assertMayAddTo(int $prodId): void
    {
        if (!$this->structureManager->getElementById($prodId) instanceof zxProdElement) {
            throw new ReleaseDataException('The production of a release must be a production', 400);
        }
        $this->assert($prodId, self::ADD_PRIVILEGE);
    }
}
