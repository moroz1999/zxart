<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use Illuminate\Database\Connection;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Repositories\ProdRepository;
use ZxArt\Prods\Services\ProdCreator;
use ZxArt\Prods\Services\ProdFieldsWriter;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Prods\UseCases\AddProdFilesHandler;
use ZxArt\Prods\UseCases\BatchUploadProdsHandler;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Prods\UseCases\DeleteProdCommand;
use ZxArt\Prods\UseCases\DeleteProdFileCommand;
use ZxArt\Prods\UseCases\DeleteProdFileHandler;
use ZxArt\Prods\UseCases\DeleteProdHandler;
use ZxArt\Prods\UseCases\DeleteProdMemberCommand;
use ZxArt\Prods\UseCases\DeleteProdMemberHandler;
use ZxArt\Prods\UseCases\JoinProdsCommand;
use ZxArt\Prods\UseCases\JoinProdsHandler;
use ZxArt\Prods\UseCases\SetProdMemberCommand;
use ZxArt\Prods\UseCases\SetProdMemberHandler;
use ZxArt\Prods\UseCases\UpdateProdCommand;
use ZxArt\Prods\UseCases\UpdateProdHandler;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use authorAliasElement;
use authorElement;
use fileElement;
use privilegesManager;
use structureElement;
use structureManager;
use zxProdElement;

final class ProdUseCasesTest extends TestCase
{
    public function testDeleteRemovesElementAndReturnsItsId(): void
    {
        $element = $this->createMock(zxProdElement::class);
        $element->method('getId')->willReturn(42);
        $element->expects($this->once())->method('deleteElementData');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicDelete', 'zxProd')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxProd, 'publicDelete');

        $result = $this->handler(DeleteProdHandler::class, $element, $privilegesManager, $actionsLog)->handle(new DeleteProdCommand(42));

        self::assertSame(42, $result->id);
    }

    public function testDeleteWithoutIdIsBadRequest(): void
    {
        $handler = $this->handler(DeleteProdHandler::class, null, $this->privileges(true));

        $this->assertStatus(400, fn() => $handler->handle(new DeleteProdCommand(0)));
    }

    public function testDeleteOfMissingElementIsNotFound(): void
    {
        $handler = $this->handler(DeleteProdHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteProdCommand(42)));
    }

    public function testDeleteOfAnotherEntityTypeIsNotFound(): void
    {
        $other = $this->createMock(authorElement::class);
        $other->expects($this->never())->method('deleteElementData');
        $handler = $this->handler(DeleteProdHandler::class, $other, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteProdCommand(42)));
    }

    public function testDeleteWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $element = $this->createMock(zxProdElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $handler = $this->handler(DeleteProdHandler::class, $element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $handler->handle(new DeleteProdCommand(42)));
    }

    public function testDeleteMemberRemovesOnlyThisProdAuthorshipAndReturnsItsId(): void
    {
        $element = $this->createMock(zxProdElement::class);
        // the authorship is kept outside the element
        $element->expects($this->once())->method('persistModificationTime');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'deleteAuthor', 'zxProd')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->method('findByElement')->with(42, EntityType::Prod)->willReturn([new AuthorshipDto(42, 7, ['code'], 0, 0)]);
        $authorship->expects($this->once())
            ->method('deleteAuthorship')
            ->with(42, 7, EntityType::Prod)
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxProd, 'deleteAuthor');

        $result = $this->handler(DeleteProdMemberHandler::class,
            $element,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
        )->handle(new DeleteProdMemberCommand(42, 7));

        self::assertSame(42, $result->id);
    }

    public function testDeleteMemberWithoutAuthorIdIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $handler = $this->handler(DeleteProdMemberHandler::class,
            $this->createStub(zxProdElement::class),
            $this->privileges(true),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(400, fn() => $handler->handle(new DeleteProdMemberCommand(42, 0)));
    }

    public function testDeleteMemberOfAuthorWhoIsNotAMemberIsNotFound(): void
    {
        // /ajax/ answered success for a removal that changed nothing
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->method('findByElement')->willReturn([new AuthorshipDto(42, 8, [], 0, 0)]);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $handler = $this->handler(DeleteProdMemberHandler::class,
            $this->createStub(zxProdElement::class),
            $this->privileges(true),
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
        );

        $this->assertStatus(404, fn() => $handler->handle(new DeleteProdMemberCommand(42, 7)));
    }

    public function testDeleteMemberWithoutPrivilegeIsForbiddenAndRemovesNothing(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $handler = $this->handler(DeleteProdMemberHandler::class,
            $this->createStub(zxProdElement::class),
            $this->privileges(false),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(403, fn() => $handler->handle(new DeleteProdMemberCommand(42, 7)));
    }

    public function testDeleteFileRemovesFileOfThisProdAndReturnsItsId(): void
    {
        $file = $this->fileWithId(7);
        $file->expects($this->once())->method('deleteElementData');
        // the file keeps the privilege of the legacy action that deleted it
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(7, 'delete', 'file')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($file, StructureType::File, 'delete');

        $result = $this->handler(DeleteProdFileHandler::class, $this->prodWithFile($file), $privilegesManager, actionsLog: $actionsLog)
            ->handle(new DeleteProdFileCommand(42, 7));

        self::assertSame(42, $result->id);
    }

    public function testDeleteFileWithoutFileIdIsBadRequest(): void
    {
        $handler = $this->handler(DeleteProdFileHandler::class, $this->prodWithFile($this->createStub(fileElement::class)), $this->privileges(true));

        $this->assertStatus(400, fn() => $handler->handle(new DeleteProdFileCommand(42, 0)));
    }

    public function testDeleteFileOfAnotherElementIsNotFound(): void
    {
        $file = $this->fileWithId(8);
        $file->expects($this->never())->method('deleteElementData');
        $handler = $this->handler(DeleteProdFileHandler::class, $this->prodWithFile($file), $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteProdFileCommand(42, 7)));
    }

    public function testDeleteFileWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $file = $this->fileWithId(7);
        $file->expects($this->never())->method('deleteElementData');
        $handler = $this->handler(DeleteProdFileHandler::class, $this->prodWithFile($file), $this->privileges(false));

        $this->assertStatus(403, fn() => $handler->handle(new DeleteProdFileCommand(42, 7)));
    }

    public function testUpdateWritesTheFieldsAndLogsTheChange(): void
    {
        $element = $this->createStub(zxProdElement::class);
        $fields = new ProdFieldsDto(title: 'Dizzy');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'zxProd')
            ->willReturn(true);
        $writer = $this->createMock(ProdFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxProd, 'publicReceive');

        $result = $this->handler(UpdateProdHandler::class, $element, $privilegesManager, $actionsLog, writer: $writer)->handle(new UpdateProdCommand(42, $fields));

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(ProdFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $handler = $this->handler(UpdateProdHandler::class, $this->createStub(zxProdElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $handler->handle(new UpdateProdCommand(42, new ProdFieldsDto(title: 'Dizzy'))));
    }

    public function testUpdateOfMissingProdIsNotFound(): void
    {
        $handler = $this->handler(UpdateProdHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new UpdateProdCommand(42, new ProdFieldsDto(title: 'Dizzy'))));
    }

    public function testUpdateToBlankTitleIsBadRequest(): void
    {
        $writer = $this->createMock(ProdFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $handler = $this->handler(UpdateProdHandler::class, $this->createStub(zxProdElement::class), $this->privileges(true), writer: $writer);

        $this->assertStatus(400, fn() => $handler->handle(new UpdateProdCommand(42, new ProdFieldsDto(title: '  '))));
    }

    public function testCreateNeedsTheUploadPrivilegeOnTheRootAndReturnsTheNewId(): void
    {
        $created = $this->createStub(zxProdElement::class);
        $created->method('getId')->willReturn(55);
        $fields = new ProdFieldsDto(title: 'Dizzy');
        // the same privilege that opens the SPA's production upload form
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(1, 'batchUpload', 'zxProdsUploadForm')
            ->willReturn(true);
        $creator = $this->createMock(ProdCreator::class);
        $creator->expects($this->once())->method('create')->with($fields)->willReturn($created);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($created, StructureType::ZxProd, 'batchUpload');
        $user = $this->createMock(CurrentUser::class);
        $user->expects($this->once())->method('refreshPrivileges');

        $result = $this->handler(CreateProdHandler::class, null, $privilegesManager, $actionsLog, creator: $creator, user: $user)->handle(new CreateProdCommand($fields));

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(ProdCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateProdHandler::class, null, $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $handler->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Dizzy'))));
    }

    public function testCreateWithoutTitleIsBadRequest(): void
    {
        $creator = $this->createMock(ProdCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateProdHandler::class, null, $this->privileges(true), creator: $creator);

        $this->assertStatus(400, fn() => $handler->handle(new CreateProdCommand(new ProdFieldsDto(year: 1987))));
    }

    public function testJoinMovesTheJoinedOneIntoThisOneAndLogsIt(): void
    {
        $main = $this->createStub(zxProdElement::class);
        $joined = $this->createStub(zxProdElement::class);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->exactly(2))
            ->method('checkPrivilegesForAction')
            ->willReturnMap([
                [42, 'join', 'zxProd', true],
                [43, 'join', 'zxProd', true],
            ]);
        $prodsService = $this->createMock(ProdsService::class);
        $prodsService->expects($this->once())->method('joinDeleteZxProd')->with(42, 43, false);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($main, StructureType::ZxProd, 'join');

        $result = $this->handler(JoinProdsHandler::class,
            null,
            $privilegesManager,
            $actionsLog,
            prodsService: $prodsService,
            elementsById: [42 => $main, 43 => $joined],
        )->handle(new JoinProdsCommand(42, 43, false));

        self::assertSame(42, $result->id);
    }

    public function testJoinPassesTheReleasesOnlyChoiceOn(): void
    {
        $prodsService = $this->createMock(ProdsService::class);
        $prodsService->expects($this->once())->method('joinDeleteZxProd')->with(42, 43, true);
        $handler = $this->handler(JoinProdsHandler::class,
            null,
            $this->privileges(true),
            prodsService: $prodsService,
            elementsById: [42 => $this->createStub(zxProdElement::class), 43 => $this->createStub(zxProdElement::class)],
        );

        $handler->handle(new JoinProdsCommand(42, 43, true));
    }

    public function testJoinIntoItselfIsBadRequest(): void
    {
        $prodsService = $this->createMock(ProdsService::class);
        $prodsService->expects($this->never())->method('joinDeleteZxProd');
        $handler = $this->handler(JoinProdsHandler::class, null, $this->privileges(true), prodsService: $prodsService, elementsById: [42 => $this->createStub(zxProdElement::class)]);

        $this->assertStatus(400, fn() => $handler->handle(new JoinProdsCommand(42, 42, false)));
    }

    public function testJoinWithoutThePrivilegeOnTheJoinedOneIsForbiddenAndJoinsNothing(): void
    {
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('checkPrivilegesForAction')->willReturnMap([
            [42, 'join', 'zxProd', true],
            [43, 'join', 'zxProd', false],
        ]);
        $prodsService = $this->createMock(ProdsService::class);
        $prodsService->expects($this->never())->method('joinDeleteZxProd');
        $handler = $this->handler(JoinProdsHandler::class,
            null,
            $privilegesManager,
            prodsService: $prodsService,
            elementsById: [42 => $this->createStub(zxProdElement::class), 43 => $this->createStub(zxProdElement::class)],
        );

        $this->assertStatus(403, fn() => $handler->handle(new JoinProdsCommand(42, 43, false)));
    }

    public function testJoinOfAnotherEntityTypeIsNotFound(): void
    {
        $prodsService = $this->createMock(ProdsService::class);
        $prodsService->expects($this->never())->method('joinDeleteZxProd');
        $handler = $this->handler(JoinProdsHandler::class,
            null,
            $this->privileges(true),
            prodsService: $prodsService,
            elementsById: [42 => $this->createStub(zxProdElement::class), 43 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(404, fn() => $handler->handle(new JoinProdsCommand(42, 43, false)));
    }

    public function testSetMemberWritesTheRolesBumpsTheModificationTimeAndLogsIt(): void
    {
        $element = $this->createMock(zxProdElement::class);
        $element->expects($this->once())->method('persistModificationTime');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'zxProd')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Prod, ['code', 'music'], null, null);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxProd, 'publicReceive');

        $result = $this->handler(SetProdMemberHandler::class,
            null,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
            elementsById: [42 => $element, 7 => $this->createStub(authorElement::class)],
        )->handle(new SetProdMemberCommand(42, 7, ['code', 'music']));

        self::assertSame(42, $result->id);
    }

    public function testSetMemberTakesAnAuthorAliasAndNoRolesAsUnknown(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Prod, ['unknown'], null, null);
        $handler = $this->handler(SetProdMemberHandler::class,
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxProdElement::class), 7 => $this->createStub(authorAliasElement::class)],
        );

        $handler->handle(new SetProdMemberCommand(42, 7, []));
    }

    public function testSetMemberOfWhatIsNotAnAuthorIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetProdMemberHandler::class,
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxProdElement::class), 7 => $this->createStub(zxProdElement::class)],
        );

        $this->assertStatus(400, fn() => $handler->handle(new SetProdMemberCommand(42, 7, ['code', 'music'])));
    }

    public function testSetMemberWithARoleOfAnotherKindIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetProdMemberHandler::class,
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxProdElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(400, fn() => $handler->handle(new SetProdMemberCommand(42, 7, ['cracker'])));
    }

    public function testSetMemberWithoutThePrivilegeIsForbidden(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetProdMemberHandler::class,
            null,
            $this->privileges(false),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxProdElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(403, fn() => $handler->handle(new SetProdMemberCommand(42, 7, ['code', 'music'])));
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
        ?AuthorshipDbal $authorshipDbal = null,
        ?ProdFieldsWriter $writer = null,
        ?ProdCreator $creator = null,
        ?CurrentUser $user = null,
        ?ProdsService $prodsService = null,
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

        $actionsLog ??= $this->createStub(ActionsLogService::class);
        $authorshipDbal ??= $this->createStub(AuthorshipDbal::class);
        $transactionRunner = new TransactionRunner($this->createStub(Connection::class));
        $prodRepository = new ProdRepository($structureManager);
        $prodPrivileges = new ProdPrivileges($privilegesManager);

        foreach ([
            new CreateProdHandler(
                $transactionRunner,
                $structureManager,
                $prodPrivileges,
                $creator ?? $this->createStub(ProdCreator::class),
                $actionsLog,
                $currentUserService,
            ),
            new UpdateProdHandler($transactionRunner, $prodRepository, $prodPrivileges, $writer ?? $this->createStub(ProdFieldsWriter::class), $this->createStub(ElementAuthorsWriter::class), $this->createStub(ImportOriginsWriter::class), $this->createStub(ElementFileImporter::class), $actionsLog),
            new DeleteProdHandler($transactionRunner, $prodRepository, $prodPrivileges, $actionsLog),
            new SetProdMemberHandler($transactionRunner, $prodRepository, $prodPrivileges, $structureManager, $authorshipDbal, $actionsLog),
            new DeleteProdMemberHandler($transactionRunner, $prodRepository, $prodPrivileges, $authorshipDbal, $actionsLog),
            new AddProdFilesHandler($transactionRunner, $prodRepository, $prodPrivileges, $this->createStub(ElementFileImporter::class), $actionsLog),
            new DeleteProdFileHandler($transactionRunner, $prodRepository, $prodPrivileges, $actionsLog),
            new JoinProdsHandler($transactionRunner, $prodRepository, $prodPrivileges, $prodsService ?? $this->createStub(ProdsService::class), $actionsLog),
            $this->createStub(BatchUploadProdsHandler::class),
        ] as $handler) {
            if ($handler instanceof $class) {
                return $handler;
            }
        }

        throw new LogicException("No $class among the use cases");
    }

    private function fileWithId(int $id): fileElement&MockObject
    {
        $file = $this->createMock(fileElement::class);
        $file->method('getId')->willReturn($id);

        return $file;
    }

    private function prodWithFile(fileElement $file): zxProdElement
    {
        $element = $this->createStub(zxProdElement::class);
        $element->method('getFileSelectorPropertyNames')->willReturn(['connectedFile', 'inlayFilesSelector']);
        $element->method('getFilesList')->willReturnMap([
            ['connectedFile', []],
            ['inlayFilesSelector', [$file]],
        ]);

        return $element;
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
        } catch (ProdDataException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
            return;
        }
        self::fail('Expected ProdDataException');
    }
}
