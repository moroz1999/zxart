<?php

declare(strict_types=1);

namespace ZxArt\Users\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use ZxArt\Users\Dto\UserDataResultDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\Repositories\UserGroupRepository;
use ZxArt\Users\Services\UserGroupFieldsWriter;
use ZxArt\Users\Services\UserManagementPrivileges;

/**
 * @psalm-api
 */
final readonly class UpdateUserGroupHandler
{
    public function __construct(
        private TransactionRunner $transactionRunner,
        private UserGroupRepository $userGroupRepository,
        private UserGroupFieldsWriter $fieldsWriter,
        private UserManagementPrivileges $privileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws UserDataException
     */
    public function handle(UpdateUserGroupCommand $command): UserDataResultDto
    {
        $group = $this->userGroupRepository->get($command->groupId);
        $this->privileges->assertMaySaveGroup();
        $this->fieldsWriter->assertValid($command->fields);

        $this->transactionRunner->run(function () use ($group, $command): void {
            $this->fieldsWriter->apply($group, $command->fields);
            $this->actionsLogService->log($group, StructureType::UserGroup, UserManagementPrivileges::SAVE_GROUP);
        });

        return new UserDataResultDto(id: $command->groupId);
    }
}
