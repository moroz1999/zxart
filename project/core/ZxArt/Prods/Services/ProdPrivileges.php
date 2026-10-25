<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use privilegesManager;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Shared\StructureType;

/**
 * The production use cases check the privilege of the matching legacy action.
 *
 * @psalm-api
 */
final readonly class ProdPrivileges
{
    public function __construct(
        private privilegesManager $privilegesManager,
    ) {
    }

    /**
     * @throws ProdDataException 403
     */
    public function assert(int $elementId, string $privilege, StructureType $structureType = StructureType::ZxProd): void
    {
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $elementId,
            $privilege,
            $structureType->value,
        ) === true;
        if (!$isAllowed) {
            throw new ProdDataException('This production change is forbidden', 403);
        }
    }
}
