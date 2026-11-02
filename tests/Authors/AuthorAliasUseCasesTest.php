<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use Illuminate\Database\Connection;
use LogicException;
use PHPUnit\Framework\TestCase;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Authors\Services\AuthorAliasCreator;
use ZxArt\Authors\Services\AuthorAliasFieldsWriter;
use ZxArt\Authors\Services\AuthorAliasPrivileges;
use ZxArt\Authors\Services\AuthorMembershipsWriter;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorCommand;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorHandler;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\DeleteAuthorAliasCommand;
use ZxArt\Authors\UseCases\DeleteAuthorAliasHandler;
use ZxArt\Authors\UseCases\JoinAuthorAliasesCommand;
use ZxArt\Authors\UseCases\JoinAuthorAliasesHandler;
use ZxArt\Authors\UseCases\UpdateAuthorAliasCommand;
use ZxArt\Authors\UseCases\UpdateAuthorAliasHandler;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use authorAliasElement;
use authorElement;
use privilegesManager;
use structureElement;
use structureManager;

final class AuthorAliasUseCasesTest extends TestCase
{
    public function testDeleteRemovesElementAndReturnsItsId(): void
    {
        $element = $this->createMock(authorAliasElement::class);
        $element->method('getId')->willReturn(42);
        $element->expects($this->once())->method('deleteElementData');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicDelete', 'authorAlias')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::AuthorAlias, 'publicDelete');

        $result = $this->handler(DeleteAuthorAliasHandler::class, $element, $privilegesManager, $actionsLog)->handle(new DeleteAuthorAliasCommand(42));

        self::assertSame(42, $result->id);
    }

    public function testDeleteWithoutIdIsBadRequest(): void
    {
        $handler = $this->handler(DeleteAuthorAliasHandler::class, null, $this->privileges(true));

        $this->assertStatus(400, fn() => $handler->handle(new DeleteAuthorAliasCommand(0)));
    }

    public function testDeleteOfMissingElementIsNotFound(): void
    {
        $handler = $this->handler(DeleteAuthorAliasHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteAuthorAliasCommand(42)));
    }

    public function testDeleteOfAnotherEntityTypeIsNotFound(): void
    {
        $other = $this->createMock(authorElement::class);
        $other->expects($this->never())->method('deleteElementData');
        $handler = $this->handler(DeleteAuthorAliasHandler::class, $other, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteAuthorAliasCommand(42)));
    }

    public function testDeleteWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $element = $this->createMock(authorAliasElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $handler = $this->handler(DeleteAuthorAliasHandler::class, $element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $handler->handle(new DeleteAuthorAliasCommand(42)));
    }

    public function testConvertToAuthorReturnsCreatedEntityId(): void
    {
        $element = $this->createStub(authorAliasElement::class);
        $created = $this->createStub(authorElement::class);
        $created->method('getId')->willReturn(900);
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->once())->method('convertAliasToAuthor')->with($element)->willReturn($created);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'convertToAuthor', 'authorAlias')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::AuthorAlias, 'convertToAuthor');

        $result = $this->handler(ConvertAuthorAliasToAuthorHandler::class, $element, $privilegesManager, $actionsLog, $authorsService)->handle(new ConvertAuthorAliasToAuthorCommand(42));

        self::assertSame(900, $result->id);
    }

    public function testConvertToAuthorWithoutPrivilegeIsForbiddenAndConvertsNothing(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('convertAliasToAuthor');
        $handler = $this->handler(ConvertAuthorAliasToAuthorHandler::class, $this->createStub(authorAliasElement::class), $this->privileges(false), null, $authorsService);

        $this->assertStatus(403, fn() => $handler->handle(new ConvertAuthorAliasToAuthorCommand(42)));
    }

    public function testFailedConvertToAuthorIsServerError(): void
    {
        $authorsService = $this->createStub(AuthorsService::class);
        $authorsService->method('convertAliasToAuthor')->willReturn(null);
        $handler = $this->handler(ConvertAuthorAliasToAuthorHandler::class, $this->createStub(authorAliasElement::class), $this->privileges(true), null, $authorsService);

        $this->assertStatus(500, fn() => $handler->handle(new ConvertAuthorAliasToAuthorCommand(42)));
    }

    public function testUpdateWritesTheFieldsAndLogsTheChange(): void
    {
        $element = $this->createStub(authorAliasElement::class);
        $fields = new AuthorAliasFieldsDto(title: 'New');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'authorAlias')
            ->willReturn(true);
        $writer = $this->createMock(AuthorAliasFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::AuthorAlias, 'publicReceive');

        $result = $this->handler(UpdateAuthorAliasHandler::class, $element, $privilegesManager, $actionsLog, writer: $writer)->handle(new UpdateAuthorAliasCommand(42, $fields));

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(AuthorAliasFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $handler = $this->handler(UpdateAuthorAliasHandler::class, $this->createStub(authorAliasElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $handler->handle(new UpdateAuthorAliasCommand(42, new AuthorAliasFieldsDto(title: 'New'))));
    }

    public function testUpdateOfMissingElementIsNotFound(): void
    {
        $handler = $this->handler(UpdateAuthorAliasHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new UpdateAuthorAliasCommand(42, new AuthorAliasFieldsDto(title: 'New'))));
    }

    public function testCreateChecksTheAddPrivilegeAndReturnsTheNewId(): void
    {
        $created = $this->createStub(authorAliasElement::class);
        $created->method('getId')->willReturn(55);
        $fields = new AuthorAliasFieldsDto(title: 'Fx', authorId: 7);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(7, 'publicAdd', 'authorAlias')
            ->willReturn(true);
        $creator = $this->createMock(AuthorAliasCreator::class);
        $creator->expects($this->once())->method('create')->with($fields)->willReturn($created);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($created, StructureType::AuthorAlias, 'publicAdd');
        $user = $this->createMock(CurrentUser::class);
        $user->expects($this->once())->method('refreshPrivileges');

        $result = $this->handler(CreateAuthorAliasHandler::class, $this->createStub(authorElement::class), $privilegesManager, $actionsLog, creator: $creator, user: $user)->handle(new CreateAuthorAliasCommand($fields));

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(AuthorAliasCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateAuthorAliasHandler::class, $this->createStub(authorElement::class), $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $handler->handle(new CreateAuthorAliasCommand(new AuthorAliasFieldsDto(title: 'Fx', authorId: 7))));
    }

    public function testCreateForWhatIsNotAnAuthorIsBadRequestAndCreatesNothing(): void
    {
        $creator = $this->createMock(AuthorAliasCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateAuthorAliasHandler::class, $this->createStub(authorAliasElement::class), $this->privileges(true), creator: $creator);

        $this->assertStatus(400, fn() => $handler->handle(new CreateAuthorAliasCommand(new AuthorAliasFieldsDto(title: 'Fx', authorId: 7))));
    }

    public function testJoinMovesTheJoinedOneIntoThisOneAndLogsIt(): void
    {
        $main = $this->createStub(authorAliasElement::class);
        $joined = $this->createStub(authorAliasElement::class);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->exactly(2))
            ->method('checkPrivilegesForAction')
            ->willReturnMap([
                [42, 'join', 'authorAlias', true],
                [43, 'join', 'authorAlias', true],
            ]);
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->once())->method('joinDeleteAuthor')->with(42, 43);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($main, StructureType::AuthorAlias, 'join');

        $result = $this->handler(JoinAuthorAliasesHandler::class,
            null,
            $privilegesManager,
            $actionsLog,
            authorsService: $authorsService,
            elementsById: [42 => $main, 43 => $joined],
        )->handle(new JoinAuthorAliasesCommand(42, 43));

        self::assertSame(42, $result->id);
    }

    public function testJoinIntoItselfIsBadRequest(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('joinDeleteAuthor');
        $handler = $this->handler(JoinAuthorAliasesHandler::class, null, $this->privileges(true), authorsService: $authorsService, elementsById: [42 => $this->createStub(authorAliasElement::class)]);

        $this->assertStatus(400, fn() => $handler->handle(new JoinAuthorAliasesCommand(42, 42)));
    }

    public function testJoinWithoutThePrivilegeOnTheJoinedOneIsForbiddenAndJoinsNothing(): void
    {
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('checkPrivilegesForAction')->willReturnMap([
            [42, 'join', 'authorAlias', true],
            [43, 'join', 'authorAlias', false],
        ]);
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('joinDeleteAuthor');
        $handler = $this->handler(JoinAuthorAliasesHandler::class,
            null,
            $privilegesManager,
            authorsService: $authorsService,
            elementsById: [42 => $this->createStub(authorAliasElement::class), 43 => $this->createStub(authorAliasElement::class)],
        );

        $this->assertStatus(403, fn() => $handler->handle(new JoinAuthorAliasesCommand(42, 43)));
    }

    public function testJoinOfAnotherEntityTypeIsNotFound(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('joinDeleteAuthor');
        $handler = $this->handler(JoinAuthorAliasesHandler::class,
            null,
            $this->privileges(true),
            authorsService: $authorsService,
            elementsById: [42 => $this->createStub(authorAliasElement::class), 43 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(404, fn() => $handler->handle(new JoinAuthorAliasesCommand(42, 43)));
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
        ?AuthorAliasFieldsWriter $writer = null,
        ?AuthorAliasCreator $creator = null,
        ?CurrentUser $user = null,
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
        $actionsLog ??= $this->createStub(ActionsLogService::class);
        $transactionRunner = new TransactionRunner($this->createStub(Connection::class));
        $repository = new AuthorAliasRepository($structureManager, $authorsService);
        $privileges = new AuthorAliasPrivileges($privilegesManager, $structureManager);

        foreach ([
            new CreateAuthorAliasHandler($transactionRunner, $privileges, $creator ?? $this->createStub(AuthorAliasCreator::class), $this->createStub(AuthorMembershipsWriter::class), $this->createStub(ImportOriginsWriter::class), $actionsLog, $currentUserService),
            new UpdateAuthorAliasHandler($transactionRunner, $repository, $privileges, $writer ?? $this->createStub(AuthorAliasFieldsWriter::class), $this->createStub(AuthorMembershipsWriter::class), $this->createStub(ImportOriginsWriter::class), $actionsLog),
            new DeleteAuthorAliasHandler($transactionRunner, $repository, $privileges, $actionsLog),
            new ConvertAuthorAliasToAuthorHandler($transactionRunner, $repository, $privileges, $authorsService, $actionsLog),
            new JoinAuthorAliasesHandler($transactionRunner, $repository, $privileges, $authorsService, $actionsLog),
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
        } catch (AuthorAliasDataException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
            return;
        }
        self::fail('Expected AuthorAliasDataException');
    }
}
