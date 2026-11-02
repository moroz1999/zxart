<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Makes the download the release's file, named as given or as in the URL; its
 * structure and formats are read as for an upload. A refused download keeps
 * the file the release had.
 *
 * @psalm-api
 */
final readonly class SetReleaseFileHandler
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
    public function handle(SetReleaseFileCommand $command): ReleaseDataResultDto
    {
        $release = $this->releaseRepository->get($command->releaseId);
        $this->releasePrivileges->assert($command->releaseId, self::PRIVILEGE);

        try {
            $this->transactionRunner->run(function () use ($release, $command): void {
                $this->elementFileImporter->setMainFile($release, $command->url, $command->fileName);
                $this->actionsLogService->log($release, StructureType::ZxRelease, self::PRIVILEGE);
            });
        } catch (AttachedFileRefusedException $exception) {
            throw new ReleaseDataException($exception->getMessage(), 400, $exception);
        }

        return new ReleaseDataResultDto(id: $command->releaseId);
    }
}
