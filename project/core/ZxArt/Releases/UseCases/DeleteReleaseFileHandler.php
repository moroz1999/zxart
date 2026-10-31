<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use fileElement;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxReleaseElement;

/**
 * Deletes one file of the release's multi-file selectors (screenshots, inlays
 * and the like). The file carries the privilege itself.
 *
 * @psalm-api
 */
final readonly class DeleteReleaseFileHandler
{
    private const string PRIVILEGE = 'delete';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ReleaseRepository $releaseRepository,
        private ReleasePrivileges $releasePrivileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(DeleteReleaseFileCommand $command): ReleaseDataResultDto
    {
        $file = $this->check($command);

        $this->transactionRunner->run(function () use ($file): void {
            // logged first: the element data is gone after the deletion
            $this->actionsLogService->log($file, StructureType::File, self::PRIVILEGE);
            $file->deleteElementData();
        });

        return new ReleaseDataResultDto(id: $command->releaseId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws ReleaseDataException
     */
    public function check(DeleteReleaseFileCommand $command): fileElement
    {
        $file = $this->getSelectorFile($this->releaseRepository->get($command->releaseId), $command->fileId);
        $this->releasePrivileges->assert($command->fileId, self::PRIVILEGE, StructureType::File);

        return $file;
    }

    private function getSelectorFile(zxReleaseElement $release, int $fileId): fileElement
    {
        if ($fileId <= 0) {
            throw new ReleaseDataException('Missing required file id', 400);
        }
        foreach ($release->getFileSelectorPropertyNames() as $propertyName) {
            foreach ($release->getFilesList($propertyName) as $file) {
                if ($file->getId() === $fileId) {
                    return $file;
                }
            }
        }

        throw new ReleaseDataException('The file does not belong to this release', 404);
    }
}
