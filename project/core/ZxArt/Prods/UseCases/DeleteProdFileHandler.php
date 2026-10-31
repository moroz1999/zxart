<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use fileElement;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxProdElement;

/**
 * Deletes one file of the production's multi-file selectors (screenshots,
 * inlays and the like). The file carries the privilege itself.
 *
 * @psalm-api
 */
final readonly class DeleteProdFileHandler
{
    private const string PRIVILEGE = 'delete';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ProdRepository $prodRepository,
        private ProdPrivileges $prodPrivileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(DeleteProdFileCommand $command): ProdDataResultDto
    {
        $file = $this->check($command);

        $this->transactionRunner->run(function () use ($file): void {
            // logged first: the element data is gone after the deletion
            $this->actionsLogService->log($file, StructureType::File, self::PRIVILEGE);
            $file->deleteElementData();
        });

        return new ProdDataResultDto(id: $command->prodId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws ProdDataException
     */
    public function check(DeleteProdFileCommand $command): fileElement
    {
        $file = $this->getSelectorFile($this->prodRepository->get($command->prodId), $command->fileId);
        $this->prodPrivileges->assert($command->fileId, self::PRIVILEGE, StructureType::File);

        return $file;
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
