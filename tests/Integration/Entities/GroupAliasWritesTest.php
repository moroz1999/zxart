<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberHandler;
use ZxArt\Groups\UseCases\JoinGroupAliasesCommand;
use ZxArt\Groups\UseCases\JoinGroupAliasesHandler;
use ZxArt\Groups\UseCases\SetGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\SetGroupAliasMemberHandler;
use ZxArt\Groups\UseCases\UpdateGroupAliasCommand;
use ZxArt\Groups\UseCases\UpdateGroupAliasHandler;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;

/**
 * Group aliases written through their use cases.
 */
#[RunTestsInSeparateProcesses]
final class GroupAliasWritesTest extends EntityWriteTestCase
{
    private const string TABLE = 'module_groupalias';
    private const int COUNTRY_POLAND = 47450;
    private const array COLUMNS = ['title', 'groupId', 'startDate', 'endDate'];

    public function testCreateStoresEveryFieldUnderTheLetterOfItsName(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->createGroup('Goldmaster Guild');

        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand($this->everyField($groupId)))->id;

        self::assertSame($this->everyFieldStored($groupId), $this->storedRow(self::TABLE, $aliasId, self::COLUMNS));
        self::assertSame(['structureType' => 'letter', 'structureName' => 'w'], $this->letterOf($aliasId));
        self::assertSame('wandering-guild', $this->structureName($aliasId));
    }

    public function testCreateLetsTheCreatorKeepEditingAndIsLogged(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->createGroup('Goldmaster Guild');

        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: $groupId)))->id;

        self::assertSame(
            ['groupAlias.publicDelete', 'groupAlias.publicReceive', 'groupAlias.showPublicForm'],
            $this->allowedOnElement($aliasId),
        );
        self::assertSame(['publicAdd'], $this->loggedActions($aliasId));
    }

    public function testTheCreatorMayUpdateTheAliasRightAway(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->createGroup('Goldmaster Guild');
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: $groupId)))->id;

        $this->container->get(UpdateGroupAliasHandler::class)->handle(new UpdateGroupAliasCommand($aliasId, new GroupAliasFieldsDto(startDate: $this->date('15.03.1995'))));

        self::assertSame($this->date('15.03.1995'), $this->storedRow(self::TABLE, $aliasId, ['startDate'])['startDate']);
    }

    public function testCreateForAGroupNothingHasLoadedYet(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = (int)$this->db->table('module_group')->min('id');

        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: $groupId)))->id;

        self::assertSame($groupId, $this->storedRow(self::TABLE, $aliasId, ['groupId'])['groupId']);
    }

    public function testCreateWithoutTitleOrGroupIsRejected(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->createGroup('Goldmaster Guild');
        $createdBefore = $this->newElementIds();

        foreach ([
            new GroupAliasFieldsDto(title: '', groupId: $groupId),
            new GroupAliasFieldsDto(title: 'Wandering Guild'),
        ] as $fields) {
            try {
                $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand($fields));
                self::fail('An alias needs a name and its group');
            } catch (GroupAliasDataException $exception) {
                self::assertSame(400, $exception->getStatusCode());
            }
        }
        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testCreateForWhatIsNotAGroupWritesNothing(): void
    {
        $this->signInAsRegisteredUser();

        try {
            $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: self::COUNTRY_POLAND)));
            self::fail('A country given as the group must be rejected');
        } catch (GroupAliasDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testCreateIsForbiddenWithoutThePrivilege(): void
    {
        $this->signIn([self::PUBLIC_VISITORS]);
        $groupId = (int)$this->db->table('module_group')->min('id');
        $this->structureManager->getElementById($groupId);

        try {
            $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: $groupId)));
            self::fail('Creating must need groupAlias.publicAdd');
        } catch (GroupAliasDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testUpdateChangesTheGivenFieldsAndKeepsTheOthers(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldmaster Guild');
        $otherGroupId = $this->createGroup('Goldmaster Union');
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand($this->everyField($groupId)))->id;

        $this->container->get(UpdateGroupAliasHandler::class)->handle(new UpdateGroupAliasCommand($aliasId, new GroupAliasFieldsDto(groupId: $otherGroupId, endDate: $this->date('31.12.2001'))));

        self::assertSame(
            array_replace($this->everyFieldStored($groupId), ['groupId' => $otherGroupId, 'endDate' => $this->date('31.12.2001')]),
            $this->storedRow(self::TABLE, $aliasId, self::COLUMNS),
        );
        self::assertSame(['publicAdd', 'publicReceive'], $this->loggedActions($aliasId));
    }

    public function testUpdateClearsTheDatesWithZero(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldmaster Guild');
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand($this->everyField($groupId)))->id;

        $this->container->get(UpdateGroupAliasHandler::class)->handle(new UpdateGroupAliasCommand($aliasId, new GroupAliasFieldsDto(startDate: 0, endDate: 0)));

        self::assertSame(
            array_replace($this->everyFieldStored($groupId), ['startDate' => 0, 'endDate' => 0]),
            $this->storedRow(self::TABLE, $aliasId, self::COLUMNS),
        );
    }

    public function testRenameKeepsTheAliasInItsLetter(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldmaster Guild');
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: $groupId)))->id;

        $this->container->get(UpdateGroupAliasHandler::class)->handle(new UpdateGroupAliasCommand($aliasId, new GroupAliasFieldsDto(title: 'Zeta Guild')));

        self::assertSame('Zeta Guild', $this->storedRow(self::TABLE, $aliasId, ['title'])['title']);
        self::assertSame('zeta-guild', $this->structureName($aliasId));
        self::assertSame(['structureType' => 'letter', 'structureName' => 'w'], $this->letterOf($aliasId));
    }

    public function testUpdateToWhatIsNotAGroupChangesNothing(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldmaster Guild');
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand($this->everyField($groupId)))->id;

        try {
            $this->container->get(UpdateGroupAliasHandler::class)->handle(new UpdateGroupAliasCommand($aliasId, new GroupAliasFieldsDto(title: 'Zeta Guild', groupId: self::COUNTRY_POLAND)));
            self::fail('A country given as the group must be rejected');
        } catch (GroupAliasDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame($this->everyFieldStored($groupId), $this->storedRow(self::TABLE, $aliasId, self::COLUMNS));
    }

    public function testUpdateIsForbiddenWithoutThePrivilegeOnTheAlias(): void
    {
        $this->signInAsRegisteredUser();
        $aliasId = (int)$this->db->table(self::TABLE)->min('id');
        $stored = $this->db->table(self::TABLE)->where('id', '=', $aliasId)->get();

        try {
            $this->container->get(UpdateGroupAliasHandler::class)->handle(new UpdateGroupAliasCommand($aliasId, new GroupAliasFieldsDto(title: 'Zeta Guild')));
            self::fail('Updating must need groupAlias.publicReceive');
        } catch (GroupAliasDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame($stored, $this->db->table(self::TABLE)->where('id', '=', $aliasId)->get());
    }

    public function testCreateStoresTheMembersAndPortalIds(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->createGroup('Goldmaster Guild');
        $authorId = $this->createAuthor('Goldmaster Writer');

        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(
            new GroupAliasFieldsDto(title: 'Goldmaster Crew', groupId: $groupId),
            members: [new AuthorshipInputDto($authorId, ['cracker'], 0, $this->date('1996-01-01'))],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '7002')],
        ))->id;

        self::assertSame(
            [['roles' => ['cracker'], 'startDate' => 0, 'endDate' => $this->date('1996-01-01')]],
            $this->authorshipRecords($aliasId, $authorId, 'group'),
        );
        self::assertSame([['origin' => 'zxdb', 'importId' => '7002']], $this->storedImportOrigins($aliasId));
    }

    public function testUpdateReplacesTheListsGivenAndKeepsTheOnesLeftOut(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldmaster Guild');
        $authorId = $this->createAuthor('Goldmaster Writer');
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(
            new GroupAliasFieldsDto(title: 'Goldmaster Crew', groupId: $groupId),
            members: [new AuthorshipInputDto($authorId, ['cracker'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '7002')],
        ))->id;

        $this->container->get(UpdateGroupAliasHandler::class)->handle(new UpdateGroupAliasCommand(
            $aliasId,
            new GroupAliasFieldsDto(title: 'Zeta Crew'),
            members: [],
            importOrigins: [new ImportOriginDto(ImportOrigin::Pouet, '42')],
        ));

        self::assertSame(['title' => 'Zeta Crew'], $this->storedRow(self::TABLE, $aliasId, ['title']));
        self::assertSame([], $this->authorshipRecords($aliasId, $authorId, 'group'));
        self::assertSame([['origin' => 'pouet', 'importId' => '42']], $this->storedImportOrigins($aliasId));
    }

    public function testAWrongMemberRefusesTheWholeUpdate(): void
    {
        $this->signInAsEditor();
        $groupId = $this->createGroup('Goldmaster Guild');
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Goldmaster Crew', groupId: $groupId)))->id;

        $this->assertRefused(400, GroupAliasDataException::class, fn() => $this->container->get(UpdateGroupAliasHandler::class)->handle(new UpdateGroupAliasCommand(
            $aliasId,
            new GroupAliasFieldsDto(title: 'Zeta Crew'),
            members: [new AuthorshipInputDto($groupId)],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '7002')],
        )));

        self::assertSame(['title' => 'Goldmaster Crew'], $this->storedRow(self::TABLE, $aliasId, ['title']));
        self::assertSame([], $this->storedImportOrigins($aliasId));
    }

    public function testAWrongRoleRefusesTheCreateAndCreatesNothing(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->createGroup('Goldmaster Guild');
        $authorId = $this->createAuthor('Goldmaster Writer');
        $createdBefore = $this->newElementIds();

        $this->assertRefused(400, GroupAliasDataException::class, fn() => $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(
            new GroupAliasFieldsDto(title: 'Goldmaster Crew', groupId: $groupId),
            members: [new AuthorshipInputDto($authorId, ['music'])],
        )));

        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testJoinMovesTheMembersOfTheJoinedAliasAndDeletesIt(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['groupAlias', 'join']]);
        $groupId = $this->createGroup('Goldmaster Guild');
        $mainId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: $groupId)))->id;
        $joinedId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Union', groupId: $groupId)))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $this->addAuthorship($authorId, $joinedId, 'group');

        $this->container->get(JoinGroupAliasesHandler::class)->handle(new JoinGroupAliasesCommand($mainId, $joinedId));

        self::assertFalse($this->elementExists($joinedId));
        self::assertContains(['authorId' => $authorId, 'elementId' => $mainId, 'type' => 'group'], $this->authorshipRows($authorId));
        self::assertSame(['publicAdd', 'join'], $this->loggedActions($mainId));
    }

    public function testJoinIsForbiddenWithoutThePrivilegeAndJoinsNothing(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->createGroup('Goldmaster Guild');
        $mainId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Guild', groupId: $groupId)))->id;
        $joinedId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'Wandering Union', groupId: $groupId)))->id;

        try {
            $this->container->get(JoinGroupAliasesHandler::class)->handle(new JoinGroupAliasesCommand($mainId, $joinedId));
            self::fail('Joining must need groupAlias.join');
        } catch (GroupAliasDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertTrue($this->elementExists($joinedId));
    }

    public function testMembersAreSetAndRemovedAndMoveTheModificationTime(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['groupAlias', 'publicReceive'], ['groupAlias', 'deleteAuthor']]);
        $groupId = $this->createGroup('Goldmaster Guild');
        $aliasId = $this->container->get(CreateGroupAliasHandler::class)->handle(new CreateGroupAliasCommand($this->everyField($groupId)))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;
        $this->ageModificationTime($aliasId);

        $this->container->get(SetGroupAliasMemberHandler::class)->handle(new SetGroupAliasMemberCommand($aliasId, $authorId, ['coder'], $this->date('15.03.1995'), null));

        self::assertSame(
            [['roles' => ['coder'], 'startDate' => $this->date('15.03.1995'), 'endDate' => 0]],
            $this->authorshipRecords($aliasId, $authorId, 'group'),
        );
        self::assertGreaterThan(1, $this->modificationTime($aliasId));

        $this->ageModificationTime($aliasId);
        $this->container->get(DeleteGroupAliasMemberHandler::class)->handle(new DeleteGroupAliasMemberCommand($aliasId, $authorId));

        self::assertSame([], $this->authorshipRecords($aliasId, $authorId, 'group'));
        self::assertGreaterThan(1, $this->modificationTime($aliasId));
    }

    private function signInAsRegisteredUser(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
    }

    /**
     * A registered user who may also edit every group alias.
     */
    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['groupAlias', 'publicReceive']]);
    }

    private function createAuthor(string $title): int
    {
        return $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: $title)))->id;
    }

    private function createGroup(string $title): int
    {
        return $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: $title)))->id;
    }

    private function everyField(int $groupId): GroupAliasFieldsDto
    {
        return new GroupAliasFieldsDto(
            title: 'Wandering Guild',
            groupId: $groupId,
            startDate: $this->date('15.03.1995'),
            endDate: $this->date('01.06.1999'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function everyFieldStored(int $groupId): array
    {
        return [
            'title' => 'Wandering Guild',
            'groupId' => $groupId,
            'startDate' => $this->date('15.03.1995'),
            'endDate' => $this->date('01.06.1999'),
        ];
    }
}
