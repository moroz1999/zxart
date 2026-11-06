<?php

declare(strict_types=1);

namespace ZxArt\Users\Services;

use privilegesManager;
use structureManager;
use ZxArt\Shared\StructureType;
use ZxArt\Users\Exception\UserDataException;

/**
 * The privilege checks of user management. Each is held on the public root,
 * so it answers "may this user do it at all", not for one user or group:
 *
 * - `root.manageUsers` — the users list, the dashboard, a user's details and
 *   form, the groups a user belongs to;
 * - `user.ban` — banning, the action the admin panel has always used;
 * - `userGroup.receive`, `userGroup.delete` — saving and deleting groups.
 *
 * @psalm-api
 */
final readonly class UserManagementPrivileges
{
    public const string MANAGE_USERS = 'manageUsers';
    public const string BAN = 'ban';
    public const string SAVE_GROUP = 'receive';
    public const string DELETE_GROUP = 'delete';

    public function __construct(
        private privilegesManager $privilegesManager,
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws UserDataException 403
     */
    public function assertMayManageUsers(): void
    {
        $this->assert(StructureType::Root, self::MANAGE_USERS);
    }

    /**
     * @throws UserDataException 403
     */
    public function assertMayBan(): void
    {
        $this->assert(StructureType::User, self::BAN);
    }

    /**
     * @throws UserDataException 403
     */
    public function assertMaySaveGroup(): void
    {
        $this->assert(StructureType::UserGroup, self::SAVE_GROUP);
    }

    /**
     * @throws UserDataException 403
     */
    public function assertMayDeleteGroup(): void
    {
        $this->assert(StructureType::UserGroup, self::DELETE_GROUP);
    }

    /**
     * The groups list is read by the users screens and by the groups screen,
     * so either privilege opens it.
     *
     * @throws UserDataException 403
     */
    public function assertMayReadGroups(): void
    {
        $isAllowed = $this->isAllowed(StructureType::Root, self::MANAGE_USERS)
            || $this->isAllowed(StructureType::UserGroup, self::SAVE_GROUP);
        if (!$isAllowed) {
            throw new UserDataException('This user management action is forbidden', 403);
        }
    }

    /**
     * @throws UserDataException 403
     */
    private function assert(StructureType $structureType, string $action): void
    {
        $isAllowed = $this->isAllowed($structureType, $action);
        if (!$isAllowed) {
            throw new UserDataException('This user management action is forbidden', 403);
        }
    }

    private function isAllowed(StructureType $structureType, string $action): bool
    {
        // the root's privileges are worked out as it loads
        $this->structureManager->getRootElement();

        return $this->privilegesManager->checkPrivilegesForAction(
            $this->structureManager->getRootElementId(),
            $action,
            $structureType->value,
        ) === true;
    }
}
