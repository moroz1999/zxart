<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use authorAliasElement;
use authorElement;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Authors\Services\AuthorAliasCreator;
use ZxArt\Authors\Services\AuthorAliasDataService;
use ZxArt\Authors\Services\AuthorAliasFieldsWriter;
use ZxArt\Authors\Services\AuthorAliasPrivileges;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Authors\UseCases\ConvertAuthorAliasToAuthorHandler;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Authors\UseCases\DeleteAuthorAliasHandler;
use ZxArt\Authors\UseCases\JoinAuthorAliasesHandler;
use ZxArt\Authors\UseCases\UpdateAuthorAliasHandler;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

final class AuthorAliasDataServiceTest extends TestCase
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
        $element = $this->createMock(authorAliasElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $service = $this->createService($element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $service->delete(42));
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

        $result = $this->createService($element, $privilegesManager, $actionsLog, $authorsService)->convertToAuthor(42);

        self::assertSame(900, $result->id);
    }

    public function testConvertToAuthorWithoutPrivilegeIsForbiddenAndConvertsNothing(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('convertAliasToAuthor');
        $service = $this->createService($this->createStub(authorAliasElement::class), $this->privileges(false), null, $authorsService);

        $this->assertStatus(403, fn() => $service->convertToAuthor(42));
    }

    public function testFailedConvertToAuthorIsServerError(): void
    {
        $authorsService = $this->createStub(AuthorsService::class);
        $authorsService->method('convertAliasToAuthor')->willReturn(null);
        $service = $this->createService($this->createStub(authorAliasElement::class), $this->privileges(true), null, $authorsService);

        $this->assertStatus(500, fn() => $service->convertToAuthor(42));
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

        $result = $this->createService($element, $privilegesManager, $actionsLog, writer: $writer)->update(42, $fields);

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(AuthorAliasFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $service = $this->createService($this->createStub(authorAliasElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $service->update(42, new AuthorAliasFieldsDto(title: 'New')));
    }

    public function testUpdateOfMissingElementIsNotFound(): void
    {
        $service = $this->createService(null, $this->privileges(true));

        $this->assertStatus(404, fn() => $service->update(42, new AuthorAliasFieldsDto(title: 'New')));
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

        $result = $this->createService($this->createStub(authorElement::class), $privilegesManager, $actionsLog, creator: $creator, user: $user)->create($fields);

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(AuthorAliasCreator::class);
        $creator->expects($this->never())->method('create');
        $service = $this->createService($this->createStub(authorElement::class), $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $service->create(new AuthorAliasFieldsDto(title: 'Fx', authorId: 7)));
    }

    public function testCreateForWhatIsNotAnAuthorIsBadRequestAndCreatesNothing(): void
    {
        $creator = $this->createMock(AuthorAliasCreator::class);
        $creator->expects($this->never())->method('create');
        $service = $this->createService($this->createStub(authorAliasElement::class), $this->privileges(true), creator: $creator);

        $this->assertStatus(400, fn() => $service->create(new AuthorAliasFieldsDto(title: 'Fx', authorId: 7)));
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

        $result = $this->createService(
            null,
            $privilegesManager,
            $actionsLog,
            authorsService: $authorsService,
            elementsById: [42 => $main, 43 => $joined],
        )->join(42, 43);

        self::assertSame(42, $result->id);
    }

    public function testJoinIntoItselfIsBadRequest(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('joinDeleteAuthor');
        $service = $this->createService(null, $this->privileges(true), authorsService: $authorsService, elementsById: [42 => $this->createStub(authorAliasElement::class)]);

        $this->assertStatus(400, fn() => $service->join(42, 42));
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
        $service = $this->createService(
            null,
            $privilegesManager,
            authorsService: $authorsService,
            elementsById: [42 => $this->createStub(authorAliasElement::class), 43 => $this->createStub(authorAliasElement::class)],
        );

        $this->assertStatus(403, fn() => $service->join(42, 43));
    }

    public function testJoinOfAnotherEntityTypeIsNotFound(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('joinDeleteAuthor');
        $service = $this->createService(
            null,
            $this->privileges(true),
            authorsService: $authorsService,
            elementsById: [42 => $this->createStub(authorAliasElement::class), 43 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(404, fn() => $service->join(42, 43));
    }

    private function createService(
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?AuthorsService $authorsService = null,
        ?AuthorAliasFieldsWriter $writer = null,
        ?AuthorAliasCreator $creator = null,
        ?CurrentUser $user = null,
        ?array $elementsById = null,
    ): AuthorAliasDataService {
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

        return new AuthorAliasDataService(
            new CreateAuthorAliasHandler($transactionRunner, $privileges, $creator ?? $this->createStub(AuthorAliasCreator::class), $actionsLog, $currentUserService),
            new UpdateAuthorAliasHandler($transactionRunner, $repository, $privileges, $writer ?? $this->createStub(AuthorAliasFieldsWriter::class), $actionsLog),
            new DeleteAuthorAliasHandler($transactionRunner, $repository, $privileges, $actionsLog),
            new ConvertAuthorAliasToAuthorHandler($transactionRunner, $repository, $privileges, $authorsService, $actionsLog),
            new JoinAuthorAliasesHandler($transactionRunner, $repository, $privileges, $authorsService, $actionsLog),
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
        } catch (AuthorAliasDataException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
            return;
        }
        self::fail('Expected AuthorAliasDataException');
    }
}
