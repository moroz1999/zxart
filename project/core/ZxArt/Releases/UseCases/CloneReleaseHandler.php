<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleaseCloner;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Makes a new release of the same production from this one; the user who
 * clones it may edit the copy right away.
 *
 * @psalm-api
 */
final readonly class CloneReleaseHandler
{
    private const string PRIVILEGE = 'clone';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ReleaseRepository $releaseRepository,
        private ReleasePrivileges $releasePrivileges,
        private ReleaseCloner $releaseCloner,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(CloneReleaseCommand $command): ReleaseDataResultDto
    {
        $source = $this->releaseRepository->get($command->releaseId);
        $this->releasePrivileges->assert($command->releaseId, self::PRIVILEGE);

        $cloneId = $this->transactionRunner->run(function () use ($source): int {
            $clone = $this->releaseCloner->clone($source);
            $this->actionsLogService->log($source, StructureType::ZxRelease, self::PRIVILEGE);
            return $clone->getId();
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new ReleaseDataResultDto(id: $cloneId);
    }
}
