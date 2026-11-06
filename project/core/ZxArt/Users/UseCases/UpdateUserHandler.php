<?php

declare(strict_types=1);

namespace ZxArt\Users\UseCases;

use userElement;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use ZxArt\Users\Dbal\UserAccountsDbal;
use ZxArt\Users\Dbal\UserGroupsDbal;
use ZxArt\Users\Dto\UserDataResultDto;
use ZxArt\Users\Dto\UserFieldsDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\Repositories\UserRepository;
use ZxArt\Users\Services\UserManagementPrivileges;

/**
 * Saves the management form of a user: the given fields, and the groups the
 * user belongs to when a list is given.
 *
 * @psalm-api
 */
final readonly class UpdateUserHandler
{
    public function __construct(
        private TransactionRunner $transactionRunner,
        private UserRepository $userRepository,
        private UserManagementPrivileges $privileges,
        private UserAccountsDbal $userAccountsDbal,
        private UserGroupsDbal $userGroupsDbal,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws UserDataException
     */
    public function handle(UpdateUserCommand $command): UserDataResultDto
    {
        $user = $this->userRepository->get($command->userId);
        $this->privileges->assertMayManageUsers();
        $fields = $command->fields;
        $userName = $fields->userName === null ? null : trim($fields->userName);
        $email = $fields->email === null ? null : trim($fields->email);
        $this->assertUserNameIsFree($userName, $command->userId);
        $this->assertEmailIsFree($email, $command->userId);
        $this->assertGroupsExist($fields->groupIds);

        $this->transactionRunner->run(function () use ($user, $fields, $userName, $email): void {
            $this->applyFields($user, $fields, $userName, $email);
            if ($fields->groupIds !== null) {
                $this->userGroupsDbal->replaceGroupsOfUser($user->getId(), $fields->groupIds);
            }
            $this->actionsLogService->log($user, StructureType::User, UserManagementPrivileges::MANAGE_USERS);
        });

        return new UserDataResultDto(id: $command->userId);
    }

    private function applyFields(userElement $user, UserFieldsDto $fields, ?string $userName, ?string $email): void
    {
        ElementFields::assign($user, [
            'userName' => $userName,
            'email' => $email,
            'verified' => $fields->verified,
            'comments' => $fields->commentsAllowed,
            'showemail' => $fields->showEmail,
            'vip' => $fields->vip,
            'supporter' => $fields->supporter,
            'volunteer' => $fields->volunteer,
        ]);
        if ($userName !== null) {
            $user->structureName = $userName;
        }
        $this->userRepository->save($user);
    }

    /**
     * @throws UserDataException
     */
    private function assertUserNameIsFree(?string $userName, int $userId): void
    {
        if ($userName === null) {
            return;
        }
        if ($userName === '') {
            throw new UserDataException('A user needs a name', 400);
        }
        $isTaken = $this->userAccountsDbal->isUserNameTaken($userName, $userId);
        if ($isTaken) {
            throw new UserDataException('Another user already has this name', 400);
        }
    }

    /**
     * @throws UserDataException
     */
    private function assertEmailIsFree(?string $email, int $userId): void
    {
        if ($email === null || $email === '') {
            return;
        }
        $isTaken = $this->userAccountsDbal->isEmailTaken($email, $userId);
        if ($isTaken) {
            throw new UserDataException('Another user already has this email', 400);
        }
    }

    /**
     * @param list<int>|null $groupIds
     * @throws UserDataException
     */
    private function assertGroupsExist(?array $groupIds): void
    {
        if ($groupIds === null) {
            return;
        }
        $unknownIds = array_diff($groupIds, $this->userGroupsDbal->findExistingGroupIds($groupIds));
        if ($unknownIds !== []) {
            throw new UserDataException('No user group ' . implode(', ', $unknownIds), 400);
        }
    }
}
