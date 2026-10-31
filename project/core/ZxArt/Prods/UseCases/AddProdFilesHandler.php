<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Shared\Dto\AddedFilesDto;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxProdElement;

/**
 * Downloads the URLs into the production's list of that kind. A refused
 * download, or a file the list does not take, adds none of them.
 *
 * @psalm-api
 */
final readonly class AddProdFilesHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ProdRepository $prodRepository,
        private ProdPrivileges $prodPrivileges,
        private ElementFileImporter $elementFileImporter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(AddProdFilesCommand $command): AddedFilesDto
    {
        $prod = $this->check($command);

        $fileIds = $this->transactionRunner->run(function () use ($prod, $command): array {
            try {
                $fileIds = $this->elementFileImporter->addFiles($prod, $command->kind, $command->urls);
            } catch (AttachedFileRefusedException $exception) {
                throw new ProdDataException($exception->getMessage(), 400, $exception);
            }
            $this->prodRepository->touch($prod);
            $this->actionsLogService->log($prod, StructureType::ZxProd, self::PRIVILEGE);
            return $fileIds;
        });

        return new AddedFilesDto(id: $command->prodId, fileIds: $fileIds);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws ProdDataException
     */
    public function check(AddProdFilesCommand $command): zxProdElement
    {
        $prod = $this->prodRepository->get($command->prodId);
        $this->prodPrivileges->assert($command->prodId, self::PRIVILEGE);
        if ($command->urls === []) {
            throw new ProdDataException('No file to add', 400);
        }

        return $prod;
    }
}
