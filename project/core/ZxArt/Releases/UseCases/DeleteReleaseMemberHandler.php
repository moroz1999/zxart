<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class DeleteReleaseMemberHandler
{
    private const string PRIVILEGE = 'deleteAuthor';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ReleaseRepository $releaseRepository,
        private ReleasePrivileges $releasePrivileges,
        private AuthorshipRepository $authorshipRepository,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(DeleteReleaseMemberCommand $command): ReleaseDataResultDto
    {
        $release = $this->releaseRepository->get($command->releaseId);
        $this->releasePrivileges->assert($command->releaseId, self::PRIVILEGE);
        if ($command->authorId <= 0) {
            throw new ReleaseDataException('Missing required author id', 400);
        }

        $isDeleted = $this->transactionRunner->run(function () use ($release, $command): bool {
            $isDeleted = $this->authorshipRepository->deleteAuthorship($command->releaseId, $command->authorId, EntityType::Release);
            if ($isDeleted) {
                $this->releaseRepository->touch($release);
                $this->actionsLogService->log($release, StructureType::ZxRelease, self::PRIVILEGE);
            }
            return $isDeleted;
        });
        if (!$isDeleted) {
            throw new ReleaseDataException('The author is not a member of this release', 404);
        }

        return new ReleaseDataResultDto(id: $command->releaseId);
    }
}
