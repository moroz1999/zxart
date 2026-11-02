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
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupCreator;
use ZxArt\Groups\Services\GroupFieldsWriter;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Groups\UseCases\ConvertGroupToAuthorCommand;
use ZxArt\Groups\UseCases\ConvertGroupToAuthorHandler;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Groups\UseCases\DeleteGroupCommand;
use ZxArt\Groups\UseCases\DeleteGroupHandler;
use ZxArt\Groups\UseCases\DeleteGroupMemberCommand;
use ZxArt\Groups\UseCases\DeleteGroupMemberHandler;
use ZxArt\Groups\UseCases\JoinGroupsCommand;
use ZxArt\Groups\UseCases\JoinGroupsHandler;
use ZxArt\Groups\UseCases\SetGroupMemberCommand;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Groups\UseCases\UpdateGroupCommand;
use ZxArt\Groups\UseCases\UpdateGroupHandler;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Shared\Elements\ElementImageWriter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use authorAliasElement;
use authorElement;
use groupElement;
use privilegesManager;
use structureElement;
use structureManager;

final class GroupUseCasesTest extends TestCase
{
    public function testDeleteRemovesElementAndReturnsItsId(): void
    {
        $element = $this->createMock(groupElement::class);
        $element->method('getId')->willReturn(42);
        $element->expects($this->once())->method('deleteElementData');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicDelete', 'group')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Group, 'publicDelete');

        $result = $this->handler(DeleteGroupHandler::class, $element, $privilegesManager, $actionsLog)->handle(new DeleteGroupCommand(42));

        self::assertSame(42, $result->id);
    }

    public function testDeleteWithoutIdIsBadRequest(): void
    {
        $handler = $this->handler(DeleteGroupHandler::class, null, $this->privileges(true));

        $this->assertStatus(400, fn() => $handler->handle(new DeleteGroupCommand(0)));
    }

    public function testDeleteOfMissingElementIsNotFound(): void
    {
        $handler = $this->handler(DeleteGroupHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteGroupCommand(42)));
    }

    public function testDeleteOfAnotherEntityTypeIsNotFound(): void
    {
        $other = $this->createMock(authorElement::class);
        $other->expects($this->never())->method('deleteElementData');
        $handler = $this->handler(DeleteGroupHandler::class, $other, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteGroupCommand(42)));
    }

    public function testDeleteWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $element = $this->createMock(groupElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $handler = $this->handler(DeleteGroupHandler::class, $element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $handler->handle(new DeleteGroupCommand(42)));
    }

    public function testConvertToAuthorReturnsCreatedEntityId(): void
    {
        $element = $this->createStub(groupElement::class);
        $created = $this->createStub(authorElement::class);
        $created->method('getId')->willReturn(900);
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->once())->method('convertGroupToAuthor')->with($element)->willReturn($created);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'convertToAuthor', 'group')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Group, 'convertToAuthor');

        $result = $this->handler(ConvertGroupToAuthorHandler::class, $element, $privilegesManager, $actionsLog, $authorsService)->handle(new ConvertGroupToAuthorCommand(42));

        self::assertSame(900, $result->id);
    }

    public function testConvertToAuthorWithoutPrivilegeIsForbiddenAndConvertsNothing(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('convertGroupToAuthor');
        $handler = $this->handler(ConvertGroupToAuthorHandler::class, $this->createStub(groupElement::class), $this->privileges(false), null, $authorsService);

        $this->assertStatus(403, fn() => $handler->handle(new ConvertGroupToAuthorCommand(42)));
    }

    public function testFailedConvertToAuthorIsServerError(): void
    {
        $authorsService = $this->createStub(AuthorsService::class);
        $authorsService->method('convertGroupToAuthor')->willReturn(null);
        $handler = $this->handler(ConvertGroupToAuthorHandler::class, $this->createStub(groupElement::class), $this->privileges(true), null, $authorsService);

        $this->assertStatus(500, fn() => $handler->handle(new ConvertGroupToAuthorCommand(42)));
    }

    public function testDeleteMemberRemovesOnlyThisGroupAuthorshipAndReturnsItsId(): void
    {
        $element = $this->createMock(groupElement::class);
        // the authorship is kept outside the element
        $element->expects($this->once())->method('persistModificationTime');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'deleteAuthor', 'group')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->method('findByElement')->with(42, EntityType::Group)->willReturn([new AuthorshipDto(42, 7, [], 0, 0)]);
        $authorship->expects($this->once())
            ->method('deleteAuthorship')
            ->with(42, 7, EntityType::Group)
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Group, 'deleteAuthor');

        $result = $this->handler(DeleteGroupMemberHandler::class,
            $element,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
        )->handle(new DeleteGroupMemberCommand(42, 7));

        self::assertSame(42, $result->id);
    }

    public function testDeleteMemberWithoutAuthorIdIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $handler = $this->handler(DeleteGroupMemberHandler::class,
            $this->createStub(groupElement::class),
            $this->privileges(true),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(400, fn() => $handler->handle(new DeleteGroupMemberCommand(42, 0)));
    }

    public function testDeleteMemberOfAuthorWhoIsNotAMemberIsNotFound(): void
    {
        // /ajax/ answered success for a removal that changed nothing
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->method('findByElement')->willReturn([new AuthorshipDto(42, 8, [], 0, 0)]);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $handler = $this->handler(DeleteGroupMemberHandler::class,
            $this->createStub(groupElement::class),
            $this->privileges(true),
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
        );

        $this->assertStatus(404, fn() => $handler->handle(new DeleteGroupMemberCommand(42, 7)));
    }

    public function testDeleteMemberWithoutPrivilegeIsForbiddenAndRemovesNothing(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $handler = $this->handler(DeleteGroupMemberHandler::class,
            $this->createStub(groupElement::class),
            $this->privileges(false),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(403, fn() => $handler->handle(new DeleteGroupMemberCommand(42, 7)));
    }

    public function testUpdateWritesTheFieldsAndLogsTheChange(): void
    {
        $element = $this->createStub(groupElement::class);
        $fields = new GroupFieldsDto(title: 'New');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'group')
            ->willReturn(true);
        $writer = $this->createMock(GroupFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Group, 'publicReceive');

        $result = $this->handler(UpdateGroupHandler::class, $element, $privilegesManager, $actionsLog, writer: $writer)->handle(new UpdateGroupCommand(42, $fields));

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(GroupFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $handler = $this->handler(UpdateGroupHandler::class, $this->createStub(groupElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $handler->handle(new UpdateGroupCommand(42, new GroupFieldsDto(title: 'New'))));
    }

    public function testUpdateOfMissingElementIsNotFound(): void
    {
        $handler = $this->handler(UpdateGroupHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new UpdateGroupCommand(42, new GroupFieldsDto(title: 'New'))));
    }

    public function testCreateChecksTheAddPrivilegeAndReturnsTheNewId(): void
    {
        $created = $this->createStub(groupElement::class);
        $created->method('getId')->willReturn(55);
        $fields = new GroupFieldsDto(title: 'Fenomen');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(1, 'publicAdd', 'group')
            ->willReturn(true);
        $creator = $this->createMock(GroupCreator::class);
        $creator->expects($this->once())->method('create')->with($fields)->willReturn($created);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($created, StructureType::Group, 'publicAdd');
        $user = $this->createMock(CurrentUser::class);
        $user->expects($this->once())->method('refreshPrivileges');

        $result = $this->handler(CreateGroupHandler::class, null, $privilegesManager, $actionsLog, creator: $creator, user: $user)->handle(new CreateGroupCommand($fields));

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(GroupCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateGroupHandler::class, null, $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $handler->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Fenomen'))));
    }

    public function testJoinMovesTheJoinedOneIntoThisOneAndLogsIt(): void
    {
        $main = $this->createStub(groupElement::class);
        $joined = $this->createStub(groupElement::class);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->exactly(2))
            ->method('checkPrivilegesForAction')
            ->willReturnMap([
                [42, 'join', 'group', true],
                [43, 'join', 'group', true],
            ]);
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->once())->method('joinDeleteGroup')->with(42, 43);
        $groupsService->expects($this->never())->method('joinGroupAsAlias');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($main, StructureType::Group, 'join');

        $result = $this->handler(JoinGroupsHandler::class,
            null,
            $privilegesManager,
            $actionsLog,
            groupsService: $groupsService,
            elementsById: [42 => $main, 43 => $joined],
        )->handle(new JoinGroupsCommand(42, 43, false));

        self::assertSame(42, $result->id);
    }

    public function testJoinAsAliasKeepsTheJoinedOneAsAnAlias(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->once())->method('joinGroupAsAlias')->with(42, 43);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $handler = $this->handler(JoinGroupsHandler::class,
            null,
            $this->privileges(true),
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupElement::class), 43 => $this->createStub(groupElement::class)],
        );

        $result = $handler->handle(new JoinGroupsCommand(42, 43, true));

        self::assertSame(42, $result->id);
    }

    public function testJoinIntoItselfIsBadRequest(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $handler = $this->handler(JoinGroupsHandler::class, null, $this->privileges(true), groupsService: $groupsService, elementsById: [42 => $this->createStub(groupElement::class)]);

        $this->assertStatus(400, fn() => $handler->handle(new JoinGroupsCommand(42, 42, false)));
    }

    public function testJoinWithoutThePrivilegeOnTheJoinedOneIsForbiddenAndJoinsNothing(): void
    {
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('checkPrivilegesForAction')->willReturnMap([
            [42, 'join', 'group', true],
            [43, 'join', 'group', false],
        ]);
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $handler = $this->handler(JoinGroupsHandler::class,
            null,
            $privilegesManager,
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupElement::class), 43 => $this->createStub(groupElement::class)],
        );

        $this->assertStatus(403, fn() => $handler->handle(new JoinGroupsCommand(42, 43, false)));
    }

    public function testJoinOfAnotherEntityTypeIsNotFound(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $handler = $this->handler(JoinGroupsHandler::class,
            null,
            $this->privileges(true),
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupElement::class), 43 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(404, fn() => $handler->handle(new JoinGroupsCommand(42, 43, false)));
    }

    public function testSetMemberWritesTheRolesBumpsTheModificationTimeAndLogsIt(): void
    {
        $element = $this->createMock(groupElement::class);
        $element->expects($this->once())->method('persistModificationTime');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'group')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Group, ['coder', 'musician'], 788918400, 0);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Group, 'publicReceive');

        $result = $this->handler(SetGroupMemberHandler::class,
            null,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
            elementsById: [42 => $element, 7 => $this->createStub(authorElement::class)],
        )->handle(new SetGroupMemberCommand(42, 7, ['coder', 'musician'], 788918400, 0));

        self::assertSame(42, $result->id);
    }

    public function testSetMemberTakesAnAuthorAliasAndNoRolesAsUnknown(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Group, ['unknown'], null, null);
        $handler = $this->handler(SetGroupMemberHandler::class,
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(groupElement::class), 7 => $this->createStub(authorAliasElement::class)],
        );

        $handler->handle(new SetGroupMemberCommand(42, 7, []));
    }

    public function testSetMemberOfWhatIsNotAnAuthorIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetGroupMemberHandler::class,
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(groupElement::class), 7 => $this->createStub(groupElement::class)],
        );

        $this->assertStatus(400, fn() => $handler->handle(new SetGroupMemberCommand(42, 7, ['coder', 'musician'])));
    }

    public function testSetMemberWithARoleOfAnotherKindIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetGroupMemberHandler::class,
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(groupElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(400, fn() => $handler->handle(new SetGroupMemberCommand(42, 7, ['code'])));
    }

    public function testSetMemberWithoutThePrivilegeIsForbidden(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetGroupMemberHandler::class,
            null,
            $this->privileges(false),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(groupElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(403, fn() => $handler->handle(new SetGroupMemberCommand(42, 7, ['coder', 'musician'])));
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
        ?AuthorsService $authorsService = null,
        ?AuthorshipDbal $authorshipDbal = null,
        ?GroupFieldsWriter $writer = null,
        ?GroupCreator $creator = null,
        ?CurrentUser $user = null,
        ?GroupsService $groupsService = null,
        ?array $elementsById = null,
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

        $authorsService ??= $this->createStub(AuthorsService::class);
        $groupsService ??= $this->createStub(GroupsService::class);
        $actionsLog ??= $this->createStub(ActionsLogService::class);
        $authorshipDbal ??= $this->createStub(AuthorshipDbal::class);
        $transactionRunner = new TransactionRunner($this->createStub(Connection::class));
        $repository = new GroupRepository($structureManager, $groupsService);
        $privileges = new GroupPrivileges($privilegesManager, $structureManager);

        foreach ([
            new CreateGroupHandler($transactionRunner, $privileges, $creator ?? $this->createStub(GroupCreator::class), $repository, $this->createStub(ElementAuthorsWriter::class), $this->createStub(ImportOriginsWriter::class), $this->createStub(ElementImageWriter::class), $actionsLog, $currentUserService),
            new UpdateGroupHandler($transactionRunner, $repository, $privileges, $writer ?? $this->createStub(GroupFieldsWriter::class), $this->createStub(ElementAuthorsWriter::class), $this->createStub(ImportOriginsWriter::class), $this->createStub(ElementImageWriter::class), $actionsLog),
            new DeleteGroupHandler($transactionRunner, $repository, $privileges, $actionsLog),
            new ConvertGroupToAuthorHandler($transactionRunner, $repository, $privileges, $authorsService, $actionsLog),
            new JoinGroupsHandler($transactionRunner, $repository, $privileges, $groupsService, $actionsLog),
            new SetGroupMemberHandler($transactionRunner, $repository, $privileges, $structureManager, $authorshipDbal, $actionsLog),
            new DeleteGroupMemberHandler($transactionRunner, $repository, $privileges, $authorshipDbal, $actionsLog),
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
        } catch (GroupDataException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
            return;
        }
        self::fail('Expected GroupDataException');
    }
}
