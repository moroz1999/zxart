<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Moves everything of the joined group into this one. The joined group is
 * deleted, or with `asAlias` kept as an alias of this one.
 *
 * @psalm-api
 */
final readonly class JoinGroupsHandler
{
    private const string PRIVILEGE = 'join';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupRepository $groupRepository,
        private GroupPrivileges $groupPrivileges,
        private GroupsService $groupsService,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(JoinGroupsCommand $command): GroupDataResultDto
    {
        if ($command->joinedId === $command->groupId) {
            throw new GroupDataException('A group cannot be joined into itself', 400);
        }
        $group = $this->groupRepository->get($command->groupId);
        $this->groupPrivileges->assert($command->groupId, self::PRIVILEGE);
        $this->groupRepository->get($command->joinedId);
        $this->groupPrivileges->assert($command->joinedId, self::PRIVILEGE);

        $this->transactionRunner->run(function () use ($group, $command): void {
            $this->actionsLogService->log($group, StructureType::Group, self::PRIVILEGE);
            if ($command->asAlias) {
                $this->groupsService->joinGroupAsAlias($command->groupId, $command->joinedId);
            } else {
                $this->groupsService->joinDeleteGroup($command->groupId, $command->joinedId);
            }
        });

        return new GroupDataResultDto(id: $command->groupId);
    }
}
