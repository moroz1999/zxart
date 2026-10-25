<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use authorAliasElement;
use authorElement;
use groupElement;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupCreator;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Groups\Services\GroupFieldsWriter;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Groups\UseCases\ConvertGroupToAuthorHandler;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Groups\UseCases\DeleteGroupHandler;
use ZxArt\Groups\UseCases\DeleteGroupMemberHandler;
use ZxArt\Groups\UseCases\JoinGroupsHandler;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Groups\UseCases\UpdateGroupHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

final class GroupDataServiceTest extends TestCase
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

        $result = $this->createService($element, $privilegesManager, $actionsLog)->delete(42);

        self::assertSame(42, $result->id);
    }

    public function testDeleteWithoutIdIsBadRequest(): void
    {
        $service = $this->createService(null, $this->privileges(true));

        $this->assertStatus(400, fn() => $service->delete(0));
    }

    public function testDeleteOfMissingElementIsNotFound(): void
    {
        $service = $this->createService(null, $this->privileges(true));

        $this->assertStatus(404, fn() => $service->delete(42));
    }

    public function testDeleteOfAnotherEntityTypeIsNotFound(): void
    {
        $other = $this->createMock(authorElement::class);
        $other->expects($this->never())->method('deleteElementData');
        $service = $this->createService($other, $this->privileges(true));

        $this->assertStatus(404, fn() => $service->delete(42));
    }

    public function testDeleteWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $element = $this->createMock(groupElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $service = $this->createService($element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $service->delete(42));
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

        $result = $this->createService($element, $privilegesManager, $actionsLog, $authorsService)->convertToAuthor(42);

        self::assertSame(900, $result->id);
    }

    public function testConvertToAuthorWithoutPrivilegeIsForbiddenAndConvertsNothing(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('convertGroupToAuthor');
        $service = $this->createService($this->createStub(groupElement::class), $this->privileges(false), null, $authorsService);

        $this->assertStatus(403, fn() => $service->convertToAuthor(42));
    }

    public function testFailedConvertToAuthorIsServerError(): void
    {
        $authorsService = $this->createStub(AuthorsService::class);
        $authorsService->method('convertGroupToAuthor')->willReturn(null);
        $service = $this->createService($this->createStub(groupElement::class), $this->privileges(true), null, $authorsService);

        $this->assertStatus(500, fn() => $service->convertToAuthor(42));
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
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->once())
            ->method('deleteAuthorship')
            ->with(42, 7, EntityType::Group)
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Group, 'deleteAuthor');

        $result = $this->createService(
            $element,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipRepository: $authorship,
        )->deleteMember(42, 7);

        self::assertSame(42, $result->id);
    }

    public function testDeleteMemberWithoutAuthorIdIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $service = $this->createService(
            $this->createStub(groupElement::class),
            $this->privileges(true),
            authorshipRepository: $authorship,
        );

        $this->assertStatus(400, fn() => $service->deleteMember(42, 0));
    }

    public function testDeleteMemberOfAuthorWhoIsNotAMemberIsNotFound(): void
    {
        // /ajax/ answered success for a removal that changed nothing
        $authorship = $this->createStub(AuthorshipRepository::class);
        $authorship->method('deleteAuthorship')->willReturn(false);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $service = $this->createService(
            $this->createStub(groupElement::class),
            $this->privileges(true),
            actionsLog: $actionsLog,
            authorshipRepository: $authorship,
        );

        $this->assertStatus(404, fn() => $service->deleteMember(42, 7));
    }

    public function testDeleteMemberWithoutPrivilegeIsForbiddenAndRemovesNothing(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $service = $this->createService(
            $this->createStub(groupElement::class),
            $this->privileges(false),
            authorshipRepository: $authorship,
        );

        $this->assertStatus(403, fn() => $service->deleteMember(42, 7));
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

        $result = $this->createService($element, $privilegesManager, $actionsLog, writer: $writer)->update(42, $fields);

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(GroupFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $service = $this->createService($this->createStub(groupElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $service->update(42, new GroupFieldsDto(title: 'New')));
    }

    public function testUpdateOfMissingElementIsNotFound(): void
    {
        $service = $this->createService(null, $this->privileges(true));

        $this->assertStatus(404, fn() => $service->update(42, new GroupFieldsDto(title: 'New')));
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

        $result = $this->createService(null, $privilegesManager, $actionsLog, creator: $creator, user: $user)->create($fields);

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(GroupCreator::class);
        $creator->expects($this->never())->method('create');
        $service = $this->createService(null, $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $service->create(new GroupFieldsDto(title: 'Fenomen')));
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

        $result = $this->createService(
            null,
            $privilegesManager,
            $actionsLog,
            groupsService: $groupsService,
            elementsById: [42 => $main, 43 => $joined],
        )->join(42, 43, false);

        self::assertSame(42, $result->id);
    }

    public function testJoinAsAliasKeepsTheJoinedOneAsAnAlias(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->once())->method('joinGroupAsAlias')->with(42, 43);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $service = $this->createService(
            null,
            $this->privileges(true),
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupElement::class), 43 => $this->createStub(groupElement::class)],
        );

        $result = $service->join(42, 43, true);

        self::assertSame(42, $result->id);
    }

    public function testJoinIntoItselfIsBadRequest(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $service = $this->createService(null, $this->privileges(true), groupsService: $groupsService, elementsById: [42 => $this->createStub(groupElement::class)]);

        $this->assertStatus(400, fn() => $service->join(42, 42, false));
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
        $service = $this->createService(
            null,
            $privilegesManager,
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupElement::class), 43 => $this->createStub(groupElement::class)],
        );

        $this->assertStatus(403, fn() => $service->join(42, 43, false));
    }

    public function testJoinOfAnotherEntityTypeIsNotFound(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $service = $this->createService(
            null,
            $this->privileges(true),
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupElement::class), 43 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(404, fn() => $service->join(42, 43, false));
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
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Group, ['coder', 'musician'], 788918400, 0);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Group, 'publicReceive');

        $result = $this->createService(
            null,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipRepository: $authorship,
            elementsById: [42 => $element, 7 => $this->createStub(authorElement::class)],
        )->setMember(42, 7, ['coder', 'musician'], 788918400, 0);

        self::assertSame(42, $result->id);
    }

    public function testSetMemberTakesAnAuthorAliasAndNoRolesAsUnknown(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Group, ['unknown'], null, null);
        $service = $this->createService(
            null,
            $this->privileges(true),
            authorshipRepository: $authorship,
            elementsById: [42 => $this->createStub(groupElement::class), 7 => $this->createStub(authorAliasElement::class)],
        );

        $service->setMember(42, 7, []);
    }

    public function testSetMemberOfWhatIsNotAnAuthorIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $service = $this->createService(
            null,
            $this->privileges(true),
            authorshipRepository: $authorship,
            elementsById: [42 => $this->createStub(groupElement::class), 7 => $this->createStub(groupElement::class)],
        );

        $this->assertStatus(400, fn() => $service->setMember(42, 7, ['coder', 'musician']));
    }

    public function testSetMemberWithARoleOfAnotherKindIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $service = $this->createService(
            null,
            $this->privileges(true),
            authorshipRepository: $authorship,
            elementsById: [42 => $this->createStub(groupElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(400, fn() => $service->setMember(42, 7, ['code']));
    }

    public function testSetMemberWithoutThePrivilegeIsForbidden(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $service = $this->createService(
            null,
            $this->privileges(false),
            authorshipRepository: $authorship,
            elementsById: [42 => $this->createStub(groupElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(403, fn() => $service->setMember(42, 7, ['coder', 'musician']));
    }

    private function createService(
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?AuthorsService $authorsService = null,
        ?AuthorshipRepository $authorshipRepository = null,
        ?GroupFieldsWriter $writer = null,
        ?GroupCreator $creator = null,
        ?CurrentUser $user = null,
        ?GroupsService $groupsService = null,
        ?array $elementsById = null,
    ): GroupDataService {
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
        $authorshipRepository ??= $this->createStub(AuthorshipRepository::class);
        $transactionRunner = new TransactionRunner($this->createStub(Connection::class));
        $repository = new GroupRepository($structureManager, $groupsService);
        $privileges = new GroupPrivileges($privilegesManager, $structureManager);

        return new GroupDataService(
            new CreateGroupHandler($transactionRunner, $privileges, $creator ?? $this->createStub(GroupCreator::class), $actionsLog, $currentUserService),
            new UpdateGroupHandler($transactionRunner, $repository, $privileges, $writer ?? $this->createStub(GroupFieldsWriter::class), $actionsLog),
            new DeleteGroupHandler($transactionRunner, $repository, $privileges, $actionsLog),
            new ConvertGroupToAuthorHandler($transactionRunner, $repository, $privileges, $authorsService, $actionsLog),
            new JoinGroupsHandler($transactionRunner, $repository, $privileges, $groupsService, $actionsLog),
            new SetGroupMemberHandler($transactionRunner, $repository, $privileges, $structureManager, $authorshipRepository, $actionsLog),
            new DeleteGroupMemberHandler($transactionRunner, $repository, $privileges, $authorshipRepository, $actionsLog),
        );
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
