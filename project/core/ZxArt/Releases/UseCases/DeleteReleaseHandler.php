<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxReleaseElement;

/**
 * @psalm-api
 */
final readonly class DeleteReleaseHandler
{
    private const string PRIVILEGE = 'publicDelete';

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
    public function handle(DeleteReleaseCommand $command): ReleaseDataResultDto
    {
        $release = $this->check($command);

        $this->transactionRunner->run(function () use ($release): void {
            // logged first: the element data is gone after the deletion
            $this->actionsLogService->log($release, StructureType::ZxRelease, self::PRIVILEGE);
            $this->releaseRepository->delete($release);
        });

        return new ReleaseDataResultDto(id: $command->releaseId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws ReleaseDataException
     */
    public function check(DeleteReleaseCommand $command): zxReleaseElement
    {
        $release = $this->releaseRepository->get($command->releaseId);
        $this->releasePrivileges->assert($command->releaseId, self::PRIVILEGE);

        return $release;
    }
}
