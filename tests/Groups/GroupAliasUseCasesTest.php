<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use Illuminate\Database\Connection;
use LogicException;
use PHPUnit\Framework\TestCase;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasCreator;
use ZxArt\Groups\Services\GroupAliasFieldsWriter;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Groups\UseCases\ConvertGroupAliasToGroupCommand;
use ZxArt\Groups\UseCases\ConvertGroupAliasToGroupHandler;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\DeleteGroupAliasCommand;
use ZxArt\Groups\UseCases\DeleteGroupAliasHandler;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberHandler;
use ZxArt\Groups\UseCases\JoinGroupAliasesCommand;
use ZxArt\Groups\UseCases\JoinGroupAliasesHandler;
use ZxArt\Groups\UseCases\SetGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\SetGroupAliasMemberHandler;
use ZxArt\Groups\UseCases\UpdateGroupAliasCommand;
use ZxArt\Groups\UseCases\UpdateGroupAliasHandler;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use privilegesManager;
use structureElement;
use structureManager;

final class GroupAliasUseCasesTest extends TestCase
{
    public function testDeleteRemovesElementAndReturnsItsId(): void
    {
        $element = $this->createMock(groupAliasElement::class);
        $element->method('getId')->willReturn(42);
        $element->expects($this->once())->method('deleteElementData');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicDelete', 'groupAlias')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::GroupAlias, 'publicDelete');

        $result = $this->handler(DeleteGroupAliasHandler::class, $element, $privilegesManager, $actionsLog)->handle(new DeleteGroupAliasCommand(42));

        self::assertSame(42, $result->id);
    }

    public function testDeleteWithoutIdIsBadRequest(): void
    {
        $handler = $this->handler(DeleteGroupAliasHandler::class, null, $this->privileges(true));

        $this->assertStatus(400, fn() => $handler->handle(new DeleteGroupAliasCommand(0)));
    }

    public function testDeleteOfMissingElementIsNotFound(): void
    {
        $handler = $this->handler(DeleteGroupAliasHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteGroupAliasCommand(42)));
    }

    public function testDeleteOfAnotherEntityTypeIsNotFound(): void
    {
        $other = $this->createMock(groupElement::class);
        $other->expects($this->never())->method('deleteElementData');
        $handler = $this->handler(DeleteGroupAliasHandler::class, $other, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteGroupAliasCommand(42)));
    }

    public function testDeleteWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $element = $this->createMock(groupAliasElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $handler = $this->handler(DeleteGroupAliasHandler::class, $element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $handler->handle(new DeleteGroupAliasCommand(42)));
    }

    public function testConvertToGroupReturnsCreatedEntityId(): void
    {
        $element = $this->createStub(groupAliasElement::class);
        $created = $this->createStub(groupElement::class);
        $created->method('getId')->willReturn(900);
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->once())->method('convertGroupAliasToGroup')->with($element)->willReturn($created);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'convertToGroup', 'groupAlias')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::GroupAlias, 'convertToGroup');

        $result = $this->handler(ConvertGroupAliasToGroupHandler::class, $element, $privilegesManager, $actionsLog, $groupsService)->handle(new ConvertGroupAliasToGroupCommand(42));

        self::assertSame(900, $result->id);
    }

    public function testConvertToGroupWithoutPrivilegeIsForbiddenAndConvertsNothing(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('convertGroupAliasToGroup');
        $handler = $this->handler(ConvertGroupAliasToGroupHandler::class, $this->createStub(groupAliasElement::class), $this->privileges(false), null, $groupsService);

        $this->assertStatus(403, fn() => $handler->handle(new ConvertGroupAliasToGroupCommand(42)));
    }

    public function testFailedConvertToGroupIsServerError(): void
    {
        $groupsService = $this->createStub(GroupsService::class);
        $groupsService->method('convertGroupAliasToGroup')->willReturn(null);
        $handler = $this->handler(ConvertGroupAliasToGroupHandler::class, $this->createStub(groupAliasElement::class), $this->privileges(true), null, $groupsService);

        $this->assertStatus(500, fn() => $handler->handle(new ConvertGroupAliasToGroupCommand(42)));
    }

    public function testUpdateWritesTheFieldsAndLogsTheChange(): void
    {
        $element = $this->createStub(groupAliasElement::class);
        $fields = new GroupAliasFieldsDto(title: 'New');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'groupAlias')
            ->willReturn(true);
        $writer = $this->createMock(GroupAliasFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::GroupAlias, 'publicReceive');

        $result = $this->handler(UpdateGroupAliasHandler::class, $element, $privilegesManager, $actionsLog, writer: $writer)->handle(new UpdateGroupAliasCommand(42, $fields));

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(GroupAliasFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $handler = $this->handler(UpdateGroupAliasHandler::class, $this->createStub(groupAliasElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $handler->handle(new UpdateGroupAliasCommand(42, new GroupAliasFieldsDto(title: 'New'))));
    }

    public function testUpdateOfMissingElementIsNotFound(): void
    {
        $handler = $this->handler(UpdateGroupAliasHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new UpdateGroupAliasCommand(42, new GroupAliasFieldsDto(title: 'New'))));
    }

    public function testCreateChecksTheAddPrivilegeAndReturnsTheNewId(): void
    {
        $created = $this->createStub(groupAliasElement::class);
        $created->method('getId')->willReturn(55);
        $fields = new GroupAliasFieldsDto(title: 'FNM', groupId: 5);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(5, 'publicAdd', 'groupAlias')
            ->willReturn(true);
        $creator = $this->createMock(GroupAliasCreator::class);
        $creator->expects($this->once())->method('create')->with($fields)->willReturn($created);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($created, StructureType::GroupAlias, 'publicAdd');
        $user = $this->createMock(CurrentUser::class);
        $user->expects($this->once())->method('refreshPrivileges');

        $result = $this->handler(CreateGroupAliasHandler::class, $this->createStub(groupElement::class), $privilegesManager, $actionsLog, creator: $creator, user: $user)->handle(new CreateGroupAliasCommand($fields));

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(GroupAliasCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateGroupAliasHandler::class, $this->createStub(groupElement::class), $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $handler->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'FNM', groupId: 5))));
    }

    public function testCreateForWhatIsNotAGroupIsBadRequestAndCreatesNothing(): void
    {
        $creator = $this->createMock(GroupAliasCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateGroupAliasHandler::class, $this->createStub(groupAliasElement::class), $this->privileges(true), creator: $creator);

        $this->assertStatus(400, fn() => $handler->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(title: 'FNM', groupId: 5))));
    }

    public function testJoinMovesTheJoinedOneIntoThisOneAndLogsIt(): void
    {
        $main = $this->createStub(groupAliasElement::class);
        $joined = $this->createStub(groupAliasElement::class);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->exactly(2))
            ->method('checkPrivilegesForAction')
            ->willReturnMap([
                [42, 'join', 'groupAlias', true],
                [43, 'join', 'groupAlias', true],
            ]);
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->once())->method('joinDeleteGroup')->with(42, 43);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($main, StructureType::GroupAlias, 'join');

        $result = $this->handler(JoinGroupAliasesHandler::class,
            null,
            $privilegesManager,
            $actionsLog,
            groupsService: $groupsService,
            elementsById: [42 => $main, 43 => $joined],
        )->handle(new JoinGroupAliasesCommand(42, 43));

        self::assertSame(42, $result->id);
    }

    public function testJoinIntoItselfIsBadRequest(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $handler = $this->handler(JoinGroupAliasesHandler::class, null, $this->privileges(true), groupsService: $groupsService, elementsById: [42 => $this->createStub(groupAliasElement::class)]);

        $this->assertStatus(400, fn() => $handler->handle(new JoinGroupAliasesCommand(42, 42)));
    }

    public function testJoinWithoutThePrivilegeOnTheJoinedOneIsForbiddenAndJoinsNothing(): void
    {
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('checkPrivilegesForAction')->willReturnMap([
            [42, 'join', 'groupAlias', true],
            [43, 'join', 'groupAlias', false],
        ]);
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $handler = $this->handler(JoinGroupAliasesHandler::class,
            null,
            $privilegesManager,
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupAliasElement::class), 43 => $this->createStub(groupAliasElement::class)],
        );

        $this->assertStatus(403, fn() => $handler->handle(new JoinGroupAliasesCommand(42, 43)));
    }

    public function testJoinOfAnotherEntityTypeIsNotFound(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $handler = $this->handler(JoinGroupAliasesHandler::class,
            null,
            $this->privileges(true),
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupAliasElement::class), 43 => $this->createStub(groupElement::class)],
        );

        $this->assertStatus(404, fn() => $handler->handle(new JoinGroupAliasesCommand(42, 43)));
    }

    public function testSetMemberWritesTheRolesBumpsTheModificationTimeAndLogsIt(): void
    {
        $element = $this->createMock(groupAliasElement::class);
        $element->expects($this->once())->method('persistModificationTime');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'groupAlias')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Group, ['coder', 'musician'], 788918400, 0);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::GroupAlias, 'publicReceive');

        $result = $this->handler(SetGroupAliasMemberHandler::class,
            null,
            $privilegesManager,
            actionsLog: $actionsLog,
            elementsById: [42 => $element, 7 => $this->createStub(authorElement::class)],
            authorshipDbal: $authorship,
        )->handle(new SetGroupAliasMemberCommand(42, 7, ['coder', 'musician'], 788918400, 0));

        self::assertSame(42, $result->id);
    }

    public function testSetMemberTakesAnAuthorAliasAndNoRolesAsUnknown(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Group, ['unknown'], null, null);
        $handler = $this->handler(SetGroupAliasMemberHandler::class,
            null,
            $this->privileges(true),
            elementsById: [42 => $this->createStub(groupAliasElement::class), 7 => $this->createStub(authorAliasElement::class)],
            authorshipDbal: $authorship,
        );

        $handler->handle(new SetGroupAliasMemberCommand(42, 7, []));
    }

    public function testSetMemberOfWhatIsNotAnAuthorOrWithARoleOfAnotherKindIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetGroupAliasMemberHandler::class,
            null,
            $this->privileges(true),
            elementsById: [
                42 => $this->createStub(groupAliasElement::class),
                7 => $this->createStub(authorElement::class),
                8 => $this->createStub(groupElement::class),
            ],
            authorshipDbal: $authorship,
        );

        $this->assertStatus(400, fn() => $handler->handle(new SetGroupAliasMemberCommand(42, 8, ['coder'])));
        $this->assertStatus(400, fn() => $handler->handle(new SetGroupAliasMemberCommand(42, 7, ['code'])));
    }

    public function testSetMemberWithoutThePrivilegeIsForbidden(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetGroupAliasMemberHandler::class,
            null,
            $this->privileges(false),
            elementsById: [42 => $this->createStub(groupAliasElement::class), 7 => $this->createStub(authorElement::class)],
            authorshipDbal: $authorship,
        );

        $this->assertStatus(403, fn() => $handler->handle(new SetGroupAliasMemberCommand(42, 7, ['coder'])));
    }

    public function testDeleteMemberRemovesTheMembershipBumpsTheModificationTimeAndLogsIt(): void
    {
        $element = $this->createMock(groupAliasElement::class);
        $element->expects($this->once())->method('persistModificationTime');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'deleteAuthor', 'groupAlias')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->method('findByElement')->with(42, EntityType::Group)->willReturn([new AuthorshipDto(42, 7, [], 0, 0)]);
        $authorship->expects($this->once())
            ->method('deleteAuthorship')
            ->with(42, 7, EntityType::Group)
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::GroupAlias, 'deleteAuthor');

        $result = $this->handler(DeleteGroupAliasMemberHandler::class,
            $element,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
        )->handle(new DeleteGroupAliasMemberCommand(42, 7));

        self::assertSame(42, $result->id);
    }

    public function testDeleteMemberWithoutAuthorIdIsBadRequestAndOfANonMemberNotFound(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->method('findByElement')->willReturn([new AuthorshipDto(42, 8, [], 0, 0)]);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $handler = $this->handler(DeleteGroupAliasMemberHandler::class,
            $this->createStub(groupAliasElement::class),
            $this->privileges(true),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(400, fn() => $handler->handle(new DeleteGroupAliasMemberCommand(42, 0)));
        $this->assertStatus(404, fn() => $handler->handle(new DeleteGroupAliasMemberCommand(42, 7)));
    }

    public function testDeleteMemberWithoutThePrivilegeIsForbidden(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $handler = $this->handler(DeleteGroupAliasMemberHandler::class,
            $this->createStub(groupAliasElement::class),
            $this->privileges(false),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(403, fn() => $handler->handle(new DeleteGroupAliasMemberCommand(42, 7)));
    }

    /**
     * The use case of the given class, over the given doubles.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function handler(
        string $class,
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?GroupsService $groupsService = null,
        ?GroupAliasFieldsWriter $writer = null,
        ?GroupAliasCreator $creator = null,
        ?CurrentUser $user = null,
        ?array $elementsById = null,
        ?AuthorshipDbal $authorshipDbal = null,
    ): object {
        $structureManager = $this->createStub(structureManager::class);
        if ($elementsById !== null) {
            $structureManager->method('getElementById')->willReturnCallback(static fn(int $id) => $elementsById[$id] ?? null);
        } else {
            $structureManager->method('getElementById')->willReturn($element);
        }
        $structureManager->method('getRootElementId')->willReturn(1);
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user ?? $this->createStub(CurrentUser::class));

        $groupsService ??= $this->createStub(GroupsService::class);
        $actionsLog ??= $this->createStub(ActionsLogService::class);
        $authorshipDbal ??= $this->createStub(AuthorshipDbal::class);
        $transactionRunner = new TransactionRunner($this->createStub(Connection::class));
        $repository = new GroupAliasRepository($structureManager, $groupsService);
        $privileges = new GroupAliasPrivileges($privilegesManager, $structureManager);

        foreach ([
            new CreateGroupAliasHandler($transactionRunner, $privileges, $creator ?? $this->createStub(GroupAliasCreator::class), $this->createStub(ElementAuthorsWriter::class), $this->createStub(ImportOriginsWriter::class), $actionsLog, $currentUserService),
            new UpdateGroupAliasHandler($transactionRunner, $repository, $privileges, $writer ?? $this->createStub(GroupAliasFieldsWriter::class), $this->createStub(ElementAuthorsWriter::class), $this->createStub(ImportOriginsWriter::class), $actionsLog),
            new DeleteGroupAliasHandler($transactionRunner, $repository, $privileges, $actionsLog),
            new ConvertGroupAliasToGroupHandler($transactionRunner, $repository, $privileges, $groupsService, $actionsLog),
            new JoinGroupAliasesHandler($transactionRunner, $repository, $privileges, $groupsService, $actionsLog),
            new SetGroupAliasMemberHandler($transactionRunner, $repository, $privileges, $structureManager, $authorshipDbal, $actionsLog),
            new DeleteGroupAliasMemberHandler($transactionRunner, $repository, $privileges, $authorshipDbal, $actionsLog),
        ] as $handler) {
            if ($handler instanceof $class) {
                return $handler;
            }
        }

        throw new LogicException("No $class among the use cases");
    }

    private function privileges(bool $isAllowed): privilegesManager
    {
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('checkPrivilegesForAction')->willReturn($isAllowed);

        return $privilegesManager;
    }

    private function assertStatus(int $statusCode, callable $call): void
    {
        try {
            $call();
        } catch (GroupAliasDataException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
            return;
        }
        self::fail('Expected GroupAliasDataException');
    }
}
