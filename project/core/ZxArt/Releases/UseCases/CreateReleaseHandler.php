<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Services\ReleaseCreator;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class CreateReleaseHandler
{
    /** Held on the production: adding a release to it. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ReleasePrivileges $releasePrivileges,
        private ReleaseCreator $releaseCreator,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(CreateReleaseCommand $command): ReleaseDataResultDto
    {
        $prodId = $command->fields->prodId;
        if ($prodId === null || $prodId <= 0) {
            throw new ReleaseDataException('A release needs its production', 400);
        }
        $this->releasePrivileges->assertMayAddTo($prodId);

        $releaseId = $this->transactionRunner->run(function () use ($command): int {
            $release = $this->releaseCreator->create($command->fields);
            $this->actionsLogService->log($release, StructureType::ZxRelease, self::PRIVILEGE);
            return $release->getId();
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new ReleaseDataResultDto(id: $releaseId);
    }
}
