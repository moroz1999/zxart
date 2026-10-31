<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Prods\Dto\ProdDataResultDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdFieldsWriter;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxProdElement;

/**
 * Changes the fields the command carries, replaces the authors and portal ids
 * it carries and adds the uploaded files to their lists, all in one
 * transaction: a wrong reference, role or file changes nothing.
 *
 * @psalm-api
 */
final readonly class UpdateProdHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ProdRepository $prodRepository,
        private ProdPrivileges $prodPrivileges,
        private ProdFieldsWriter $prodFieldsWriter,
        private ElementAuthorsWriter $elementAuthorsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ElementFileImporter $elementFileImporter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(UpdateProdCommand $command): ProdDataResultDto
    {
        $prod = $this->check($command);

        $this->transactionRunner->run(function () use ($prod, $command): void {
            try {
                foreach ($command->files as $upload) {
                    $this->elementFileImporter->checkUpload($upload->kind, $upload->file);
                }
                $this->prodFieldsWriter->apply($prod, $command->fields);
                if ($command->authors !== null) {
                    $this->elementAuthorsWriter->replace($command->prodId, EntityType::Prod, $command->authors);
                }
                if ($command->importOrigins !== null) {
                    $this->importOriginsWriter->replace($command->prodId, EntityType::Prod, $command->importOrigins);
                }
                foreach ($command->files as $upload) {
                    $this->elementFileImporter->addUploadedFile($prod, $upload->kind, $upload->file);
                }
            } catch (InvalidReferenceException|AttachedFileRefusedException $exception) {
                throw new ProdDataException($exception->getMessage(), 400, $exception);
            }
            $this->prodRepository->touch($prod);
            $this->actionsLogService->log($prod, StructureType::ZxProd, self::PRIVILEGE);
        });

        return new ProdDataResultDto(id: $command->prodId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws ProdDataException
     */
    public function check(UpdateProdCommand $command): zxProdElement
    {
        $prod = $this->prodRepository->get($command->prodId);
        $this->prodPrivileges->assert($command->prodId, self::PRIVILEGE);
        if ($command->fields->title !== null && trim($command->fields->title) === '') {
            throw new ProdDataException('A production needs a title', 400);
        }

        return $prod;
    }
}
