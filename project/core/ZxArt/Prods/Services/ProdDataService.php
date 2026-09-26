<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Shared\StructureType;
use zxProdElement;

/**
 * Changes to a production requested through `/prod-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 */
final readonly class ProdDataService
{
    private const string DELETE_PRIVILEGE = 'publicDelete';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
    ) {
    }

    public function delete(int $prodId): ProdDataResultDto
    {
        $prod = $this->getProd($prodId, self::DELETE_PRIVILEGE);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($prod, StructureType::ZxProd, self::DELETE_PRIVILEGE);
        $prod->deleteElementData();

        return new ProdDataResultDto(id: $prodId);
    }

    private function getProd(int $prodId, string $privilege): zxProdElement
    {
        if ($prodId <= 0) {
            throw new ProdDataException('Missing required production id', 400);
        }
        $prod = $this->structureManager->getElementById($prodId);
        if (!$prod instanceof zxProdElement) {
            throw new ProdDataException('Production not found', 404);
        }
        $isAllowed = $this->privilegesManager->checkPrivilegesForAction(
            $prodId,
            $privilege,
            StructureType::ZxProd->value,
        ) === true;
        if (!$isAllowed) {
            throw new ProdDataException('This production change is forbidden', 403);
        }

        return $prod;
    }
}
