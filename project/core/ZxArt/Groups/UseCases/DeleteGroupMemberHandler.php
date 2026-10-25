<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class DeleteGroupMemberHandler
{
    private const string PRIVILEGE = 'deleteAuthor';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupRepository $groupRepository,
        private GroupPrivileges $groupPrivileges,
        private AuthorshipDbal $authorshipDbal,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(DeleteGroupMemberCommand $command): GroupDataResultDto
    {
        $group = $this->groupRepository->get($command->groupId);
        $this->groupPrivileges->assert($command->groupId, self::PRIVILEGE);
        if ($command->authorId <= 0) {
            throw new GroupDataException('Missing required author id', 400);
        }

        $isDeleted = $this->transactionRunner->run(function () use ($group, $command): bool {
            $isDeleted = $this->authorshipDbal->deleteAuthorship($command->groupId, $command->authorId, EntityType::Group);
            if ($isDeleted) {
                $this->groupRepository->touch($group);
                $this->actionsLogService->log($group, StructureType::Group, self::PRIVILEGE);
            }
            return $isDeleted;
        });
        if (!$isDeleted) {
            throw new GroupDataException('The author is not a member of this group', 404);
        }

        return new GroupDataResultDto(id: $command->groupId);
    }
}
