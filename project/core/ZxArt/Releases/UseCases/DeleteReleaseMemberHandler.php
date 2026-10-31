<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use zxReleaseElement;

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
        private AuthorshipDbal $authorshipDbal,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(DeleteReleaseMemberCommand $command): ReleaseDataResultDto
    {
        $release = $this->check($command);

        $this->transactionRunner->run(function () use ($release, $command): void {
            $this->authorshipDbal->deleteAuthorship($command->releaseId, $command->authorId, EntityType::Release);
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
    public function check(DeleteReleaseMemberCommand $command): zxReleaseElement
    {
        $release = $this->releaseRepository->get($command->releaseId);
        $this->releasePrivileges->assert($command->releaseId, self::PRIVILEGE);
        if ($command->authorId <= 0) {
            throw new ReleaseDataException('Missing required author id', 400);
        }
        foreach ($this->authorshipDbal->findByElement($command->releaseId, EntityType::Release) as $authorship) {
            if ($authorship->authorId === $command->authorId) {
                return $release;
            }
        }

        throw new ReleaseDataException('The author is not a member of this release', 404);
    }
}
