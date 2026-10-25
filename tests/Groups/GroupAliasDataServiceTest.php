<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasCreator;
use ZxArt\Groups\Services\GroupAliasDataService;
use ZxArt\Groups\Services\GroupAliasFieldsWriter;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Groups\UseCases\ConvertGroupAliasToGroupHandler;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\DeleteGroupAliasHandler;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberHandler;
use ZxArt\Groups\UseCases\JoinGroupAliasesHandler;
use ZxArt\Groups\UseCases\SetGroupAliasMemberHandler;
use ZxArt\Groups\UseCases\UpdateGroupAliasHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

final class GroupAliasDataServiceTest extends TestCase
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
        $other = $this->createMock(groupElement::class);
        $other->expects($this->never())->method('deleteElementData');
        $service = $this->createService($other, $this->privileges(true));

        $this->assertStatus(404, fn() => $service->delete(42));
    }

    public function testDeleteWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $element = $this->createMock(groupAliasElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $service = $this->createService($element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $service->delete(42));
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

        $result = $this->createService($element, $privilegesManager, $actionsLog, $groupsService)->convertToGroup(42);

        self::assertSame(900, $result->id);
    }

    public function testConvertToGroupWithoutPrivilegeIsForbiddenAndConvertsNothing(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('convertGroupAliasToGroup');
        $service = $this->createService($this->createStub(groupAliasElement::class), $this->privileges(false), null, $groupsService);

        $this->assertStatus(403, fn() => $service->convertToGroup(42));
    }

    public function testFailedConvertToGroupIsServerError(): void
    {
        $groupsService = $this->createStub(GroupsService::class);
        $groupsService->method('convertGroupAliasToGroup')->willReturn(null);
        $service = $this->createService($this->createStub(groupAliasElement::class), $this->privileges(true), null, $groupsService);

        $this->assertStatus(500, fn() => $service->convertToGroup(42));
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

        $result = $this->createService($element, $privilegesManager, $actionsLog, writer: $writer)->update(42, $fields);

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(GroupAliasFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $service = $this->createService($this->createStub(groupAliasElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $service->update(42, new GroupAliasFieldsDto(title: 'New')));
    }

    public function testUpdateOfMissingElementIsNotFound(): void
    {
        $service = $this->createService(null, $this->privileges(true));

        $this->assertStatus(404, fn() => $service->update(42, new GroupAliasFieldsDto(title: 'New')));
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

        $result = $this->createService($this->createStub(groupElement::class), $privilegesManager, $actionsLog, creator: $creator, user: $user)->create($fields);

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(GroupAliasCreator::class);
        $creator->expects($this->never())->method('create');
        $service = $this->createService($this->createStub(groupElement::class), $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $service->create(new GroupAliasFieldsDto(title: 'FNM', groupId: 5)));
    }

    public function testCreateForWhatIsNotAGroupIsBadRequestAndCreatesNothing(): void
    {
        $creator = $this->createMock(GroupAliasCreator::class);
        $creator->expects($this->never())->method('create');
        $service = $this->createService($this->createStub(groupAliasElement::class), $this->privileges(true), creator: $creator);

        $this->assertStatus(400, fn() => $service->create(new GroupAliasFieldsDto(title: 'FNM', groupId: 5)));
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

        $result = $this->createService(
            null,
            $privilegesManager,
            $actionsLog,
            groupsService: $groupsService,
            elementsById: [42 => $main, 43 => $joined],
        )->join(42, 43);

        self::assertSame(42, $result->id);
    }

    public function testJoinIntoItselfIsBadRequest(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $service = $this->createService(null, $this->privileges(true), groupsService: $groupsService, elementsById: [42 => $this->createStub(groupAliasElement::class)]);

        $this->assertStatus(400, fn() => $service->join(42, 42));
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
        $service = $this->createService(
            null,
            $privilegesManager,
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupAliasElement::class), 43 => $this->createStub(groupAliasElement::class)],
        );

        $this->assertStatus(403, fn() => $service->join(42, 43));
    }

    public function testJoinOfAnotherEntityTypeIsNotFound(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('joinDeleteGroup');
        $service = $this->createService(
            null,
            $this->privileges(true),
            groupsService: $groupsService,
            elementsById: [42 => $this->createStub(groupAliasElement::class), 43 => $this->createStub(groupElement::class)],
        );

        $this->assertStatus(404, fn() => $service->join(42, 43));
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
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Group, ['coder', 'musician'], 788918400, 0);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::GroupAlias, 'publicReceive');

        $result = $this->createService(
            null,
            $privilegesManager,
            actionsLog: $actionsLog,
            elementsById: [42 => $element, 7 => $this->createStub(authorElement::class)],
            authorshipRepository: $authorship,
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
            elementsById: [42 => $this->createStub(groupAliasElement::class), 7 => $this->createStub(authorAliasElement::class)],
            authorshipRepository: $authorship,
        );

        $service->setMember(42, 7, []);
    }

    public function testSetMemberOfWhatIsNotAnAuthorOrWithARoleOfAnotherKindIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $service = $this->createService(
            null,
            $this->privileges(true),
            elementsById: [
                42 => $this->createStub(groupAliasElement::class),
                7 => $this->createStub(authorElement::class),
                8 => $this->createStub(groupElement::class),
            ],
            authorshipRepository: $authorship,
        );

        $this->assertStatus(400, fn() => $service->setMember(42, 8, ['coder']));
        $this->assertStatus(400, fn() => $service->setMember(42, 7, ['code']));
    }

    public function testSetMemberWithoutThePrivilegeIsForbidden(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $service = $this->createService(
            null,
            $this->privileges(false),
            elementsById: [42 => $this->createStub(groupAliasElement::class), 7 => $this->createStub(authorElement::class)],
            authorshipRepository: $authorship,
        );

        $this->assertStatus(403, fn() => $service->setMember(42, 7, ['coder']));
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
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->once())
            ->method('deleteAuthorship')
            ->with(42, 7, EntityType::Group)
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::GroupAlias, 'deleteAuthor');

        $result = $this->createService(
            $element,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipRepository: $authorship,
        )->deleteMember(42, 7);

        self::assertSame(42, $result->id);
    }

    public function testDeleteMemberWithoutAuthorIdIsBadRequestAndOfANonMemberNotFound(): void
    {
        $authorship = $this->createStub(AuthorshipRepository::class);
        $authorship->method('deleteAuthorship')->willReturn(false);
        $service = $this->createService(
            $this->createStub(groupAliasElement::class),
            $this->privileges(true),
            authorshipRepository: $authorship,
        );

        $this->assertStatus(400, fn() => $service->deleteMember(42, 0));
        $this->assertStatus(404, fn() => $service->deleteMember(42, 7));
    }

    public function testDeleteMemberWithoutThePrivilegeIsForbidden(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $service = $this->createService(
            $this->createStub(groupAliasElement::class),
            $this->privileges(false),
            authorshipRepository: $authorship,
        );

        $this->assertStatus(403, fn() => $service->deleteMember(42, 7));
    }

    private function createService(
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?GroupsService $groupsService = null,
        ?GroupAliasFieldsWriter $writer = null,
        ?GroupAliasCreator $creator = null,
        ?CurrentUser $user = null,
        ?array $elementsById = null,
        ?AuthorshipRepository $authorshipRepository = null,
    ): GroupAliasDataService {
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
        $authorshipRepository ??= $this->createStub(AuthorshipRepository::class);
        $transactionRunner = new TransactionRunner($this->createStub(Connection::class));
        $repository = new GroupAliasRepository($structureManager, $groupsService);
        $privileges = new GroupAliasPrivileges($privilegesManager, $structureManager);

        return new GroupAliasDataService(
            new CreateGroupAliasHandler($transactionRunner, $privileges, $creator ?? $this->createStub(GroupAliasCreator::class), $actionsLog, $currentUserService),
            new UpdateGroupAliasHandler($transactionRunner, $repository, $privileges, $writer ?? $this->createStub(GroupAliasFieldsWriter::class), $actionsLog),
            new DeleteGroupAliasHandler($transactionRunner, $repository, $privileges, $actionsLog),
            new ConvertGroupAliasToGroupHandler($transactionRunner, $repository, $privileges, $groupsService, $actionsLog),
            new JoinGroupAliasesHandler($transactionRunner, $repository, $privileges, $groupsService, $actionsLog),
            new SetGroupAliasMemberHandler($transactionRunner, $repository, $privileges, $structureManager, $authorshipRepository, $actionsLog),
            new DeleteGroupAliasMemberHandler($transactionRunner, $repository, $privileges, $authorshipRepository, $actionsLog),
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
        } catch (GroupAliasDataException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
            return;
        }
        self::fail('Expected GroupAliasDataException');
    }
}
