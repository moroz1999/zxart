<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorCreator;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Authors\Services\AuthorFieldsWriter;
use ZxArt\Authors\Services\AuthorMembershipsWriter;
use ZxArt\Authors\Services\AuthorPrivileges;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Authors\UseCases\ConvertAuthorToGroupHandler;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Authors\UseCases\DeleteAuthorHandler;
use ZxArt\Authors\UseCases\JoinAuthorsHandler;
use ZxArt\Authors\UseCases\UpdateAuthorHandler;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Shared\Elements\ElementImageWriter;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use authorElement;
use groupElement;
use privilegesManager;
use structureElement;
use structureManager;

final class AuthorDataServiceTest extends TestCase
{
    public function testDeleteRemovesElementAndReturnsItsId(): void
    {
        $element = $this->createMock(authorElement::class);
        $element->method('getId')->willReturn(42);
        $element->expects($this->once())->method('deleteElementData');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicDelete', 'author')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Author, 'publicDelete');

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
        $element = $this->createMock(authorElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $service = $this->createService($element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $service->delete(42));
    }

    public function testConvertToGroupReturnsCreatedEntityId(): void
    {
        $element = $this->createStub(authorElement::class);
        $created = $this->createStub(groupElement::class);
        $created->method('getId')->willReturn(900);
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->once())->method('convertAuthorToGroup')->with($element)->willReturn($created);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'convertToGroup', 'author')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Author, 'convertToGroup');

        $result = $this->createService($element, $privilegesManager, $actionsLog, $groupsService)->convertToGroup(42);

        self::assertSame(900, $result->id);
    }

    public function testConvertToGroupWithoutPrivilegeIsForbiddenAndConvertsNothing(): void
    {
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->never())->method('convertAuthorToGroup');
        $service = $this->createService($this->createStub(authorElement::class), $this->privileges(false), null, $groupsService);

        $this->assertStatus(403, fn() => $service->convertToGroup(42));
    }

    public function testFailedConvertToGroupIsServerError(): void
    {
        $groupsService = $this->createStub(GroupsService::class);
        $groupsService->method('convertAuthorToGroup')->willReturn(null);
        $service = $this->createService($this->createStub(authorElement::class), $this->privileges(true), null, $groupsService);

        $this->assertStatus(500, fn() => $service->convertToGroup(42));
    }

    public function testUpdateWritesTheFieldsAndLogsTheChange(): void
    {
        $element = $this->createStub(authorElement::class);
        $fields = new AuthorFieldsDto(title: 'New');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'author')
            ->willReturn(true);
        $writer = $this->createMock(AuthorFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::Author, 'publicReceive');

        $result = $this->createService($element, $privilegesManager, $actionsLog, writer: $writer)->update(42, $fields);

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(AuthorFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $service = $this->createService($this->createStub(authorElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $service->update(42, new AuthorFieldsDto(title: 'New')));
    }

    public function testUpdateOfMissingElementIsNotFound(): void
    {
        $service = $this->createService(null, $this->privileges(true));

        $this->assertStatus(404, fn() => $service->update(42, new AuthorFieldsDto(title: 'New')));
    }

    public function testCreateChecksTheAddPrivilegeAndReturnsTheNewId(): void
    {
        $created = $this->createStub(authorElement::class);
        $created->method('getId')->willReturn(55);
        $fields = new AuthorFieldsDto(title: 'Fikus');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(1, 'publicAdd', 'author')
            ->willReturn(true);
        $creator = $this->createMock(AuthorCreator::class);
        $creator->expects($this->once())->method('create')->with($fields)->willReturn($created);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($created, StructureType::Author, 'publicAdd');
        $user = $this->createMock(CurrentUser::class);
        $user->expects($this->once())->method('refreshPrivileges');

        $result = $this->createService(null, $privilegesManager, $actionsLog, creator: $creator, user: $user)->create($fields);

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(AuthorCreator::class);
        $creator->expects($this->never())->method('create');
        $service = $this->createService(null, $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $service->create(new AuthorFieldsDto(title: 'Fikus')));
    }

    public function testJoinMovesTheJoinedOneIntoThisOneAndLogsIt(): void
    {
        $main = $this->createMock(authorElement::class);
        $main->expects($this->once())->method('recalculate');
        $joined = $this->createStub(authorElement::class);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->exactly(2))
            ->method('checkPrivilegesForAction')
            ->willReturnMap([
                [42, 'join', 'author', true],
                [43, 'join', 'author', true],
            ]);
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->once())->method('joinDeleteAuthor')->with(42, 43);
        $authorsService->expects($this->never())->method('joinAuthorAsAlias');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($main, StructureType::Author, 'join');

        $result = $this->createService(
            null,
            $privilegesManager,
            $actionsLog,
            authorsService: $authorsService,
            elementsById: [42 => $main, 43 => $joined],
        )->join(42, 43, false);

        self::assertSame(42, $result->id);
    }

    public function testJoinAsAliasKeepsTheJoinedOneAsAnAlias(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->once())->method('joinAuthorAsAlias')->with(42, 43);
        $authorsService->expects($this->never())->method('joinDeleteAuthor');
        $service = $this->createService(
            null,
            $this->privileges(true),
            authorsService: $authorsService,
            elementsById: [42 => $this->createStub(authorElement::class), 43 => $this->createStub(authorElement::class)],
        );

        $result = $service->join(42, 43, true);

        self::assertSame(42, $result->id);
    }

    public function testJoinIntoItselfIsBadRequest(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('joinDeleteAuthor');
        $service = $this->createService(null, $this->privileges(true), authorsService: $authorsService, elementsById: [42 => $this->createStub(authorElement::class)]);

        $this->assertStatus(400, fn() => $service->join(42, 42, false));
    }

    public function testJoinWithoutThePrivilegeOnTheJoinedOneIsForbiddenAndJoinsNothing(): void
    {
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('checkPrivilegesForAction')->willReturnMap([
            [42, 'join', 'author', true],
            [43, 'join', 'author', false],
        ]);
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('joinDeleteAuthor');
        $service = $this->createService(
            null,
            $privilegesManager,
            authorsService: $authorsService,
            elementsById: [42 => $this->createStub(authorElement::class), 43 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(403, fn() => $service->join(42, 43, false));
    }

    public function testJoinOfAnotherEntityTypeIsNotFound(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('joinDeleteAuthor');
        $service = $this->createService(
            null,
            $this->privileges(true),
            authorsService: $authorsService,
            elementsById: [42 => $this->createStub(authorElement::class), 43 => $this->createStub(groupElement::class)],
        );

        $this->assertStatus(404, fn() => $service->join(42, 43, false));
    }

    private function createService(
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?GroupsService $groupsService = null,
        ?AuthorFieldsWriter $writer = null,
        ?AuthorCreator $creator = null,
        ?CurrentUser $user = null,
        ?AuthorsService $authorsService = null,
        ?array $elementsById = null,
    ): AuthorDataService {
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
        $transactionRunner = new TransactionRunner($this->createStub(Connection::class));
        $repository = new AuthorRepository($structureManager, $authorsService);
        $privileges = new AuthorPrivileges($privilegesManager, $structureManager);

        return new AuthorDataService(
            new CreateAuthorHandler($transactionRunner, $privileges, $creator ?? $this->createStub(AuthorCreator::class), $repository, $this->createStub(AuthorMembershipsWriter::class), $this->createStub(ImportOriginsWriter::class), $this->createStub(ElementImageWriter::class), $actionsLog, $currentUserService),
            new UpdateAuthorHandler($transactionRunner, $repository, $privileges, $writer ?? $this->createStub(AuthorFieldsWriter::class), $this->createStub(AuthorMembershipsWriter::class), $this->createStub(ImportOriginsWriter::class), $this->createStub(ElementImageWriter::class), $actionsLog),
            new DeleteAuthorHandler($transactionRunner, $repository, $privileges, $actionsLog),
            new ConvertAuthorToGroupHandler($transactionRunner, $repository, $privileges, $groupsService, $actionsLog),
            new JoinAuthorsHandler($transactionRunner, $repository, $privileges, $authorsService, $actionsLog),
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
        } catch (AuthorDataException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
            return;
        }
        self::fail('Expected AuthorDataException');
    }
}
