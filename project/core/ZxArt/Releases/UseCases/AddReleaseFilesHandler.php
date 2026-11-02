<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\Dto\AddedFilesDto;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Downloads the URLs into the release's list of that kind. A refused download,
 * or a file the list does not take, adds none of them.
 *
 * @psalm-api
 */
final readonly class AddReleaseFilesHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ReleaseRepository $releaseRepository,
        private ReleasePrivileges $releasePrivileges,
        private ElementFileImporter $elementFileImporter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(AddReleaseFilesCommand $command): AddedFilesDto
    {
        $release = $this->releaseRepository->get($command->releaseId);
        $this->releasePrivileges->assert($command->releaseId, self::PRIVILEGE);
        if ($command->urls === []) {
            throw new ReleaseDataException('No file to add', 400);
        }

        $fileIds = $this->transactionRunner->run(function () use ($release, $command): array {
            try {
                $fileIds = $this->elementFileImporter->addFiles($release, $command->kind, $command->urls);
            } catch (AttachedFileRefusedException $exception) {
                throw new ReleaseDataException($exception->getMessage(), 400, $exception);
            }
            $this->releaseRepository->touch($release);
            $this->actionsLogService->log($release, StructureType::ZxRelease, self::PRIVILEGE);
            return $fileIds;
        });

        return new AddedFilesDto(id: $command->releaseId, fileIds: $fileIds);
    }
}
