<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Releases\Services\ReleaseFieldsWriter;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxReleaseElement;

/**
 * Changes the fields the command carries, replaces the authors and portal ids
 * it carries, adds the uploaded files to their lists and replaces the
 * release's own file, all in one transaction: a wrong reference, role or file
 * changes nothing. The release's own file is replaced by an uploaded one, or
 * taken away when the command asks for it. Moving the release to another production needs the right
 * to add releases there.
 *
 * @psalm-api
 */
final readonly class UpdateReleaseHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ReleaseRepository $releaseRepository,
        private ReleasePrivileges $releasePrivileges,
        private ReleaseFieldsWriter $releaseFieldsWriter,
        private ElementAuthorsWriter $elementAuthorsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ElementFileImporter $elementFileImporter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(UpdateReleaseCommand $command): ReleaseDataResultDto
    {
        $release = $this->check($command);

        $this->transactionRunner->run(function () use ($release, $command): void {
            try {
                foreach ($command->files as $upload) {
                    $this->elementFileImporter->checkUpload($upload->kind, $upload->file);
                }
                if ($command->file !== null) {
                    $this->elementFileImporter->checkMainFileUpload($command->file);
                }
                $this->releaseFieldsWriter->apply($release, $command->fields);
                if ($command->authors !== null) {
                    $this->elementAuthorsWriter->replace($command->releaseId, EntityType::Release, $command->authors);
                }
                if ($command->importOrigins !== null) {
                    $this->importOriginsWriter->replace($command->releaseId, EntityType::Release, $command->importOrigins);
                }
                foreach ($command->files as $upload) {
                    $this->elementFileImporter->addUploadedFile($release, $upload->kind, $upload->file);
                }
                if ($command->file !== null) {
                    $this->elementFileImporter->setUploadedMainFile($release, $command->file);
                } elseif ($command->removeFile) {
                    $this->elementFileImporter->removeMainFile($release);
                }
            } catch (InvalidReferenceException|AttachedFileRefusedException $exception) {
                throw new ReleaseDataException($exception->getMessage(), 400, $exception);
            }
            $this->releaseRepository->touch($release);
            $this->actionsLogService->log($release, StructureType::ZxRelease, self::PRIVILEGE);
        });

        return new ReleaseDataResultDto(id: $command->releaseId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws ReleaseDataException
     */
    public function check(UpdateReleaseCommand $command): zxReleaseElement
    {
        $release = $this->releaseRepository->get($command->releaseId);
        $this->releasePrivileges->assert($command->releaseId, self::PRIVILEGE);
        $prodId = $command->fields->prodId;
        if ($prodId !== null && $prodId !== $release->getProd()?->getId()) {
            $this->releasePrivileges->assertMayAddTo($prodId);
        }

        return $release;
    }
}
