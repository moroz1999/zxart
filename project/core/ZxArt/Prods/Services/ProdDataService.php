<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use fileElement;
use privilegesManager;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use zxProdElement;

/**
 * Changes to a production requested through `/prod-data/`. Each change checks the
 * privilege of the matching legacy action and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class ProdDataService
{
    private const string DELETE_PRIVILEGE = 'publicDelete';
    private const string DELETE_MEMBER_PRIVILEGE = 'deleteAuthor';
    private const string DELETE_FILE_PRIVILEGE = 'delete';

    public function __construct(
        private structureManager $structureManager,
        private privilegesManager $privilegesManager,
        private ActionsLogService $actionsLogService,
        private AuthorshipRepository $authorshipRepository,
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

    /**
     * Removes the author from the production's members.
     */
    public function deleteMember(int $prodId, int $authorId): ProdDataResultDto
    {
        $prod = $this->getProd($prodId, self::DELETE_MEMBER_PRIVILEGE);
        if ($authorId <= 0) {
            throw new ProdDataException('Missing required author id', 400);
        }
        $isDeleted = $this->authorshipRepository->deleteAuthorship($prodId, $authorId, EntityType::Prod);
        if (!$isDeleted) {
            throw new ProdDataException('The author is not a member of this production', 404);
        }
        $this->actionsLogService->log($prod, StructureType::ZxProd, self::DELETE_MEMBER_PRIVILEGE);

        return new ProdDataResultDto(id: $prodId);
    }

    /**
     * Deletes one file of the production's multi-file selectors (screenshots,
     * inlays and the like). The file carries the privilege itself.
     */
    public function deleteFile(int $prodId, int $fileId): ProdDataResultDto
    {
        $file = $this->getSelectorFile($this->findProd($prodId), $fileId);
        $this->assertAllowed($fileId, self::DELETE_FILE_PRIVILEGE, StructureType::File);
        // logged first: the element data is gone after the deletion
        $this->actionsLogService->log($file, StructureType::File, self::DELETE_FILE_PRIVILEGE);
        $file->deleteElementData();

        return new ProdDataResultDto(id: $prodId);
    }

    private function getProd(int $prodId, string $privilege): zxProdElement
    {
        $prod = $this->findProd($prodId);
        $this->assertAllowed($prodId, $privilege, StructureType::ZxProd);

        return $prod;
    }

    private function findProd(int $prodId): zxProdElement
    {
        if ($prodId <= 0) {
            throw new ProdDataException('Missing required production id', 400);
        }
        $prod = $this->structureManager->getElementById($prodId);
        if (!$prod instanceof zxProdElement) {
            throw new ProdDataException('Production not found', 404);
        }

        return $prod;
    }

    private function assertAllowed(int $elementId, string $privilege, StructureType $structureType): void
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

    private function getSelectorFile(zxProdElement $prod, int $fileId): fileElement
    {
        if ($fileId <= 0) {
            throw new ProdDataException('Missing required file id', 400);
        }
        foreach ($prod->getFileSelectorPropertyNames() as $propertyName) {
            foreach ($prod->getFilesList($propertyName) as $file) {
                if ($file->getId() === $fileId) {
                    return $file;
                }
            }
        }

        throw new ProdDataException('The file does not belong to this production', 404);
    }
}
