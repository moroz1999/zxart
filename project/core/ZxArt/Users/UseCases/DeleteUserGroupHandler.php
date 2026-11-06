<?php

declare(strict_types=1);

namespace ZxArt\Users\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use ZxArt\Users\Dto\UserDataResultDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\Repositories\UserGroupRepository;
use ZxArt\Users\Services\UserManagementPrivileges;

/**
 * Deletes a user group; its users stay, without it.
 *
 * @psalm-api
 */
final readonly class DeleteUserGroupHandler
{
    public function __construct(
        private TransactionRunner $transactionRunner,
        private UserGroupRepository $userGroupRepository,
        private UserManagementPrivileges $privileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws UserDataException
     */
    public function handle(DeleteUserGroupCommand $command): UserDataResultDto
    {
        $group = $this->userGroupRepository->get($command->groupId);
        $this->privileges->assertMayDeleteGroup();

        $this->transactionRunner->run(function () use ($group): void {
            // logged first: the element data is gone after the deletion
            $this->actionsLogService->log($group, StructureType::UserGroup, UserManagementPrivileges::DELETE_GROUP);
            $this->userGroupRepository->delete($group);
        });

        return new UserDataResultDto(id: $command->groupId);
    }
}
