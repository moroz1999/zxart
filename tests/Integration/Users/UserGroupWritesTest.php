<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Users;

require_once __DIR__ . '/UserManagementTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Users\Dto\UserGroupFieldsDto;
use ZxArt\Users\Dto\UserGroupItemDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\UseCases\CreateUserGroupCommand;
use ZxArt\Users\UseCases\CreateUserGroupHandler;
use ZxArt\Users\UseCases\DeleteUserGroupCommand;
use ZxArt\Users\UseCases\DeleteUserGroupHandler;
use ZxArt\Users\UseCases\UpdateUserGroupCommand;
use ZxArt\Users\UseCases\UpdateUserGroupHandler;
use ZxArt\Users\UserGroupsService;

/**
 * User groups listed, created, edited and deleted from the management screen.
 */
#[RunTestsInSeparateProcesses]
final class UserGroupWritesTest extends UserManagementTestCase
{
    private const array SAVE = ['userGroup', 'receive'];
    private const array DELETE = ['userGroup', 'delete'];
    private const array CREATE_IN_FOLDER = ['userGroups', 'showFullList'];
    /** The default action of a group: without it the public structure does not make the element at all. */
    private const array SEE = ['userGroup', 'showForm'];

    public function testTheListCountsTheMembersOfEveryGroup(): void
    {
        $groupId = $this->createGroup('zxmgmt-counted', 'Counted');
        $this->addToGroup($this->createUser('zxmgmt-one', 'one@zxmgmt.example', time()), $groupId);
        $this->addToGroup($this->createUser('zxmgmt-two', 'two@zxmgmt.example', time()), $groupId);

        $groups = $this->container->get(UserGroupsService::class)->getGroups();

        $found = array_values(array_filter($groups, static fn(UserGroupItemDto $group): bool => $group->id === $groupId));
        self::assertEquals([new UserGroupItemDto(id: $groupId, groupName: 'zxmgmt-counted', description: 'Counted', membersCount: 2)], $found);
    }

    public function testCreateStoresTheGroupInTheGroupsFolder(): void
    {
        $this->signInAsGroupManager([self::SAVE, self::CREATE_IN_FOLDER]);

        $groupId = $this->container->get(CreateUserGroupHandler::class)->handle(
            new CreateUserGroupCommand(new UserGroupFieldsDto(groupName: 'zxmgmt-new', description: 'Fresh')),
        )->id;

        self::assertEquals(
            ['groupName' => 'zxmgmt-new', 'description' => 'Fresh'],
            $this->storedRow('module_user_group', $groupId, ['groupName', 'description']),
        );
        self::assertSame(self::GROUPS_FOLDER, $this->structureParent($groupId)['id']);
        self::assertSame('zxmgmt-new', $this->structureName($groupId));
        self::assertSame(['receive'], $this->loggedActions($groupId));
    }

    public function testCreateWithoutAName(): void
    {
        $this->signInAsGroupManager([self::SAVE, self::CREATE_IN_FOLDER]);

        $this->assertRefused(400, UserDataException::class, fn() => $this->container->get(CreateUserGroupHandler::class)->handle(
            new CreateUserGroupCommand(new UserGroupFieldsDto(groupName: ' ', description: '')),
        ));
        self::assertSame([], $this->newElementIds());
    }

    public function testCreateWithoutThePrivilegeIsRefused(): void
    {
        $this->signInAsGroupManager([self::CREATE_IN_FOLDER]);

        $this->assertRefused(403, UserDataException::class, fn() => $this->container->get(CreateUserGroupHandler::class)->handle(
            new CreateUserGroupCommand(new UserGroupFieldsDto(groupName: 'zxmgmt-new', description: '')),
        ));
        self::assertSame([], $this->newElementIds());
    }

    public function testUpdateStoresTheNameAndTheDescription(): void
    {
        $groupId = $this->createGroup('zxmgmt-old', 'Old');
        $this->signInAsGroupManager([self::SAVE]);

        $this->container->get(UpdateUserGroupHandler::class)->handle(
            new UpdateUserGroupCommand($groupId, new UserGroupFieldsDto(groupName: 'zxmgmt-renamed', description: 'Renamed')),
        );

        self::assertEquals(
            ['groupName' => 'zxmgmt-renamed', 'description' => 'Renamed'],
            $this->storedRow('module_user_group', $groupId, ['groupName', 'description']),
        );
        self::assertSame('zxmgmt-renamed', $this->structureName($groupId));
    }

    public function testUpdateWithoutThePrivilegeIsRefused(): void
    {
        $groupId = $this->createGroup('zxmgmt-guarded', 'Guarded');
        $this->signInAsGroupManager([self::DELETE]);

        $this->assertRefused(403, UserDataException::class, fn() => $this->container->get(UpdateUserGroupHandler::class)->handle(
            new UpdateUserGroupCommand($groupId, new UserGroupFieldsDto(groupName: 'zxmgmt-renamed', description: '')),
        ));
        self::assertSame('zxmgmt-guarded', $this->structureName($groupId));
    }

    public function testAnIdOfNoGroupIsNotFound(): void
    {
        $userId = $this->createUser('zxmgmt-not-a-group', 'nag@zxmgmt.example', time());
        $this->signInAsGroupManager([self::SAVE]);

        $this->assertRefused(404, UserDataException::class, fn() => $this->container->get(UpdateUserGroupHandler::class)->handle(
            new UpdateUserGroupCommand($userId, new UserGroupFieldsDto(groupName: 'zxmgmt-renamed', description: '')),
        ));
    }

    public function testDeleteRemovesTheGroupAndItsMemberships(): void
    {
        $groupId = $this->createGroup('zxmgmt-doomed');
        $userId = $this->createUser('zxmgmt-member', 'member@zxmgmt.example', time());
        $this->addToGroup($userId, $groupId);
        $this->signInAsGroupManager([self::DELETE]);

        $this->container->get(DeleteUserGroupHandler::class)->handle(new DeleteUserGroupCommand($groupId));

        self::assertFalse($this->elementExists($groupId));
        self::assertSame([], $this->groupsOf($userId));
        self::assertSame(['delete'], $this->loggedActions($groupId));
    }

    public function testDeleteWithoutThePrivilegeIsRefused(): void
    {
        $groupId = $this->createGroup('zxmgmt-kept');
        $this->signInAsGroupManager([self::SAVE]);

        $this->assertRefused(403, UserDataException::class, fn() => $this->container->get(DeleteUserGroupHandler::class)->handle(
            new DeleteUserGroupCommand($groupId),
        ));
        self::assertTrue($this->elementExists($groupId));
    }

    /**
     * @param list<array{string, string}> $privilegesOnRoot
     */
    private function signInAsGroupManager(array $privilegesOnRoot): void
    {
        $this->signInAsManager([self::SEE, ...$privilegesOnRoot]);
    }
}
