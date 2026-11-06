<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Users;

require_once __DIR__ . '/UserManagementTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Users\Dto\UserDetailsDto;
use ZxArt\Users\Dto\UserFieldsDto;
use ZxArt\Users\Exception\UserDataException;
use ZxArt\Users\UseCases\BanUserCommand;
use ZxArt\Users\UseCases\BanUserHandler;
use ZxArt\Users\UseCases\UpdateUserCommand;
use ZxArt\Users\UseCases\UpdateUserHandler;
use ZxArt\Users\UserDetailsService;

/**
 * A user edited and banned from the management screen.
 */
#[RunTestsInSeparateProcesses]
final class UserWritesTest extends UserManagementTestCase
{
    private const array MANAGE = ['root', 'manageUsers'];
    private const array BAN = ['user', 'ban'];
    private const array FLAG_COLUMNS = ['userName', 'email', 'verified', 'comments', 'showemail', 'vip', 'supporter', 'volunteer'];

    private int $votedElementId = 0;

    public function testUpdateStoresEveryGivenField(): void
    {
        $userId = $this->createUser('zxmgmt-before', 'before@zxmgmt.example', time() - 60, verified: false);
        $this->signInAsManager([self::MANAGE]);

        $this->updateHandler()->handle(new UpdateUserCommand($userId, new UserFieldsDto(
            userName: 'zxmgmt-after',
            email: 'after@zxmgmt.example',
            verified: true,
            commentsAllowed: false,
            showEmail: true,
            vip: true,
            supporter: true,
            volunteer: true,
        )));

        self::assertEquals([
            'userName' => 'zxmgmt-after',
            'email' => 'after@zxmgmt.example',
            'verified' => 1,
            'comments' => 0,
            'showemail' => 1,
            'vip' => 1,
            'supporter' => 1,
            'volunteer' => 1,
        ], $this->storedRow('module_user', $userId, self::FLAG_COLUMNS));
        self::assertSame('zxmgmt-after', $this->structureName($userId));
        self::assertSame(['manageUsers'], $this->loggedActions($userId));
    }

    public function testUpdateLeavesTheFieldsItIsNotGiven(): void
    {
        $userId = $this->createUser('zxmgmt-kept', 'kept@zxmgmt.example', time() - 60);
        $this->signInAsManager([self::MANAGE]);

        $this->updateHandler()->handle(new UpdateUserCommand($userId, new UserFieldsDto(vip: true)));

        $row = $this->storedRow('module_user', $userId, ['userName', 'email', 'vip']);
        self::assertEquals(['userName' => 'zxmgmt-kept', 'email' => 'kept@zxmgmt.example', 'vip' => 1], $row);
    }

    public function testUpdateReplacesTheGroupsWithTheGivenOnes(): void
    {
        $keptGroup = $this->createGroup('zxmgmt-kept-group');
        $droppedGroup = $this->createGroup('zxmgmt-dropped-group');
        $addedGroup = $this->createGroup('zxmgmt-added-group');
        $userId = $this->createUser('zxmgmt-member', 'member@zxmgmt.example', time() - 60);
        $this->addToGroup($userId, $keptGroup);
        $this->addToGroup($userId, $droppedGroup);
        $this->signInAsManager([self::MANAGE]);

        $this->updateHandler()->handle(new UpdateUserCommand($userId, new UserFieldsDto(groupIds: [$keptGroup, $addedGroup])));

        self::assertSame([$keptGroup, $addedGroup], $this->groupsOf($userId));
    }

    public function testAGroupThatIsNoUserGroupIsRefused(): void
    {
        $userId = $this->createUser('zxmgmt-member', 'member@zxmgmt.example', time() - 60);
        $this->signInAsManager([self::MANAGE]);

        $this->assertRefused(400, UserDataException::class, fn() => $this->updateHandler()->handle(
            new UpdateUserCommand($userId, new UserFieldsDto(groupIds: [self::USERS_FOLDER])),
        ));
        self::assertSame([], $this->groupsOf($userId));
    }

    public function testABlankNameIsRefused(): void
    {
        $userId = $this->createUser('zxmgmt-named', 'named@zxmgmt.example', time() - 60);
        $this->signInAsManager([self::MANAGE]);

        $this->assertRefused(400, UserDataException::class, fn() => $this->updateHandler()->handle(
            new UpdateUserCommand($userId, new UserFieldsDto(userName: ' ')),
        ));
    }

    public function testANameOrAnEmailAnotherUserHasIsRefused(): void
    {
        $this->createUser('zxmgmt-taken', 'taken@zxmgmt.example', time() - 60);
        $userId = $this->createUser('zxmgmt-free', 'free@zxmgmt.example', time() - 60);
        $this->signInAsManager([self::MANAGE]);

        $this->assertRefused(400, UserDataException::class, fn() => $this->updateHandler()->handle(
            new UpdateUserCommand($userId, new UserFieldsDto(userName: 'zxmgmt-taken')),
        ));
        $this->assertRefused(400, UserDataException::class, fn() => $this->updateHandler()->handle(
            new UpdateUserCommand($userId, new UserFieldsDto(email: 'taken@zxmgmt.example')),
        ));
        self::assertEquals(
            ['userName' => 'zxmgmt-free', 'email' => 'free@zxmgmt.example'],
            $this->storedRow('module_user', $userId, ['userName', 'email']),
        );
    }

    public function testUpdateWithoutTheManagePrivilegeIsRefused(): void
    {
        $userId = $this->createUser('zxmgmt-guarded', 'guarded@zxmgmt.example', time() - 60);
        $this->signInAsManager([]);

        $this->assertRefused(403, UserDataException::class, fn() => $this->updateHandler()->handle(
            new UpdateUserCommand($userId, new UserFieldsDto(vip: true)),
        ));
        self::assertEquals(['vip' => 0], $this->storedRow('module_user', $userId, ['vip']));
    }

    public function testAnIdOfNoUserIsNotFound(): void
    {
        $groupId = $this->createGroup('zxmgmt-not-a-user');
        $this->signInAsManager([self::MANAGE]);

        $this->assertRefused(404, UserDataException::class, fn() => $this->updateHandler()->handle(
            new UpdateUserCommand($groupId, new UserFieldsDto(vip: true)),
        ));
    }

    public function testBanMarksTheUserAndRemovesTheirCommentsAndVotes(): void
    {
        $userId = $this->createUser('zxmgmt-spammer', 'spammer@zxmgmt.example', time() - 60);
        $commentId = $this->createComment($userId);
        $this->addVote($userId);
        $this->signInAsManager([self::BAN]);

        $this->banHandler()->handle(new BanUserCommand($userId));

        self::assertEquals(['banned' => 1], $this->storedRow('module_user', $userId, ['banned']));
        self::assertFalse($this->elementExists($commentId));
        self::assertSame(0, $this->db->table('votes_history')->where('userId', '=', $userId)->count());
        self::assertSame(['ban'], $this->loggedActions($userId));
    }

    public function testBanWithoutItsPrivilegeIsRefused(): void
    {
        $userId = $this->createUser('zxmgmt-spared', 'spared@zxmgmt.example', time() - 60);
        $commentId = $this->createComment($userId);
        $this->signInAsManager([self::MANAGE]);

        $this->assertRefused(403, UserDataException::class, fn() => $this->banHandler()->handle(new BanUserCommand($userId)));
        self::assertEquals(['banned' => 0], $this->storedRow('module_user', $userId, ['banned']));
        self::assertTrue($this->elementExists($commentId));
    }

    public function testDetailsCarryTheFieldsGroupsAndActivity(): void
    {
        $dateCreated = time() - 60;
        $groupId = $this->createGroup('zxmgmt-details-group');
        $userId = $this->createUser('zxmgmt-detailed', 'detailed@zxmgmt.example', $dateCreated);
        $this->addToGroup($userId, $groupId);
        $this->createComment($userId);
        $this->addVote($userId);
        $this->addVote($userId);
        $this->signInAsManager([self::MANAGE]);

        $details = $this->container->get(UserDetailsService::class)->getDetails($userId);

        self::assertEquals(new UserDetailsDto(
            id: $userId,
            userName: 'zxmgmt-detailed',
            email: 'detailed@zxmgmt.example',
            dateCreated: $dateCreated,
            verified: true,
            banned: false,
            commentsAllowed: true,
            showEmail: false,
            vip: false,
            supporter: false,
            volunteer: false,
            authorId: 0,
            authorTitle: '',
            groupIds: [$groupId],
            commentsCount: 1,
            votesCount: 2,
        ), $details);
    }

    private function createComment(int $userId): int
    {
        $commentId = (int)$this->db->table('structure_elements')->insertGetId([
            'structureType' => 'comment',
            'structureName' => 'comment',
            'structureRole' => 'content',
            'dateCreated' => time(),
            'dateModified' => time(),
            'marker' => '',
        ]);
        $this->db->table('module_comment')->insert([
            'id' => $commentId,
            'languageId' => 0,
            'author' => '',
            'content' => 'Buy now',
            'text_en' => '',
            'text_ru' => '',
            'text_es' => '',
            'is_translated' => 0,
            'email' => '',
            'website' => '',
            'userId' => $userId,
            'votes' => 0,
            'approved' => 1,
            'ipAddress' => '',
            'targetType' => '',
            'phone' => '',
        ]);
        // a comment hangs off the work it is about, which the public tree reaches
        $this->db->table('structure_links')->insert([
            'parentStructureId' => (int)$this->db->table('structure_elements')->where('structureType', '=', 'zxProd')->min('id'),
            'childStructureId' => $commentId,
            'type' => 'commentTarget',
        ]);

        return $commentId;
    }

    private function addVote(int $userId): void
    {
        // a user votes for an element once
        $this->votedElementId++;
        $this->db->table('votes_history')->insert([
            'ip' => '',
            'elementId' => $this->votedElementId,
            'value' => 5,
            'date' => time(),
            'userId' => $userId,
            'type' => 'zxPicture',
        ]);
    }

    private function updateHandler(): UpdateUserHandler
    {
        return $this->container->get(UpdateUserHandler::class);
    }

    private function banHandler(): BanUserHandler
    {
        return $this->container->get(BanUserHandler::class);
    }
}
