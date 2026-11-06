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
final readonly class CreateUserGroupHandler
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
    public function handle(CreateUserGroupCommand $command): UserDataResultDto
    {
        $this->privileges->assertMaySaveGroup();
        $this->fieldsWriter->assertValid($command->fields);

        $groupId = $this->transactionRunner->run(function () use ($command): int {
            $group = $this->userGroupRepository->create();
            $this->fieldsWriter->apply($group, $command->fields);
            $this->actionsLogService->log($group, StructureType::UserGroup, UserManagementPrivileges::SAVE_GROUP);

            return $group->getId();
        });

        return new UserDataResultDto(id: $groupId);
    }
}
