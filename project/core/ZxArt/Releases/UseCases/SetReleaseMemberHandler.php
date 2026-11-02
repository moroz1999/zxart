<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use authorAliasElement;
use authorElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Adds the author to the release's authors, or makes their roles these.
 *
 * @psalm-api
 */
final readonly class SetReleaseMemberHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ReleaseRepository $releaseRepository,
        private ReleasePrivileges $releasePrivileges,
        private structureManager $structureManager,
        private AuthorshipDbal $authorshipDbal,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(SetReleaseMemberCommand $command): ReleaseDataResultDto
    {
        $release = $this->releaseRepository->get($command->releaseId);
        $this->releasePrivileges->assert($command->releaseId, self::PRIVILEGE);
        $member = $command->authorId > 0 ? $this->structureManager->getElementById($command->authorId) : null;
        if (!$member instanceof authorElement && !$member instanceof authorAliasElement) {
            throw new ReleaseDataException('A member must be an author or an author alias', 400);
        }
        $unknownRoles = array_diff($command->roles, AuthorshipRoles::LIST);
        if ($unknownRoles !== []) {
            throw new ReleaseDataException('Unknown role: ' . implode(', ', $unknownRoles), 400);
        }

        $this->transactionRunner->run(function () use ($release, $command): void {
            $roles = $command->roles === [] ? ['unknown'] : $command->roles;
            $this->authorshipDbal->setAuthorship($command->releaseId, $command->authorId, EntityType::Release, $roles, null, null);
            $this->releaseRepository->touch($release);
            $this->actionsLogService->log($release, StructureType::ZxRelease, self::PRIVILEGE);
        });

        return new ReleaseDataResultDto(id: $command->releaseId);
    }
}
