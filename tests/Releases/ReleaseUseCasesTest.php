<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

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
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Releases\Services\ReleaseCloner;
use ZxArt\Releases\Services\ReleaseCreator;
use ZxArt\Releases\Services\ReleaseFieldsWriter;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Releases\UseCases\AddReleaseFilesHandler;
use ZxArt\Releases\UseCases\CloneReleaseCommand;
use ZxArt\Releases\UseCases\CloneReleaseHandler;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Releases\UseCases\CreateReleasesFromFilesHandler;
use ZxArt\Releases\UseCases\DeleteReleaseCommand;
use ZxArt\Releases\UseCases\DeleteReleaseFileCommand;
use ZxArt\Releases\UseCases\DeleteReleaseFileHandler;
use ZxArt\Releases\UseCases\DeleteReleaseHandler;
use ZxArt\Releases\UseCases\DeleteReleaseMemberCommand;
use ZxArt\Releases\UseCases\DeleteReleaseMemberHandler;
use ZxArt\Releases\UseCases\SetReleaseFileHandler;
use ZxArt\Releases\UseCases\SetReleaseMemberCommand;
use ZxArt\Releases\UseCases\SetReleaseMemberHandler;
use ZxArt\Releases\UseCases\UpdateReleaseCommand;
use ZxArt\Releases\UseCases\UpdateReleaseHandler;
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
use zxReleaseElement;

final class ReleaseUseCasesTest extends TestCase
{
    public function testDeleteRemovesElementAndReturnsItsId(): void
    {
        $element = $this->createMock(zxReleaseElement::class);
        $element->method('getId')->willReturn(42);
        $element->expects($this->once())->method('deleteElementData');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicDelete', 'zxRelease')
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxRelease, 'publicDelete');

        $result = $this->handler(DeleteReleaseHandler::class, $element, $privilegesManager, $actionsLog)->handle(new DeleteReleaseCommand(42));

        self::assertSame(42, $result->id);
    }

    public function testDeleteWithoutIdIsBadRequest(): void
    {
        $handler = $this->handler(DeleteReleaseHandler::class, null, $this->privileges(true));

        $this->assertStatus(400, fn() => $handler->handle(new DeleteReleaseCommand(0)));
    }

    public function testDeleteOfMissingElementIsNotFound(): void
    {
        $handler = $this->handler(DeleteReleaseHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteReleaseCommand(42)));
    }

    public function testDeleteOfAnotherEntityTypeIsNotFound(): void
    {
        $other = $this->createMock(zxProdElement::class);
        $other->expects($this->never())->method('deleteElementData');
        $handler = $this->handler(DeleteReleaseHandler::class, $other, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteReleaseCommand(42)));
    }

    public function testDeleteWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $element = $this->createMock(zxReleaseElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $handler = $this->handler(DeleteReleaseHandler::class, $element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $handler->handle(new DeleteReleaseCommand(42)));
    }

    public function testDeleteMemberRemovesOnlyThisReleaseAuthorshipAndReturnsItsId(): void
    {
        $element = $this->createMock(zxReleaseElement::class);
        // the authorship is kept outside the element
        $element->expects($this->once())->method('persistModificationTime');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'deleteAuthor', 'zxRelease')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->method('findByElement')->with(42, EntityType::Release)->willReturn([new AuthorshipDto(42, 7, [], 0, 0)]);
        $authorship->expects($this->once())
            ->method('deleteAuthorship')
            ->with(42, 7, EntityType::Release)
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxRelease, 'deleteAuthor');

        $result = $this->handler(DeleteReleaseMemberHandler::class,
            $element,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
        )->handle(new DeleteReleaseMemberCommand(42, 7));

        self::assertSame(42, $result->id);
    }

    public function testDeleteMemberWithoutAuthorIdIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $handler = $this->handler(DeleteReleaseMemberHandler::class,
            $this->createStub(zxReleaseElement::class),
            $this->privileges(true),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(400, fn() => $handler->handle(new DeleteReleaseMemberCommand(42, 0)));
    }

    public function testDeleteMemberOfAuthorWhoIsNotAMemberIsNotFound(): void
    {
        // /ajax/ answered success for a removal that changed nothing
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->method('findByElement')->willReturn([new AuthorshipDto(42, 8, [], 0, 0)]);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $handler = $this->handler(DeleteReleaseMemberHandler::class,
            $this->createStub(zxReleaseElement::class),
            $this->privileges(true),
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
        );

        $this->assertStatus(404, fn() => $handler->handle(new DeleteReleaseMemberCommand(42, 7)));
    }

    public function testDeleteMemberWithoutPrivilegeIsForbiddenAndRemovesNothing(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $handler = $this->handler(DeleteReleaseMemberHandler::class,
            $this->createStub(zxReleaseElement::class),
            $this->privileges(false),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(403, fn() => $handler->handle(new DeleteReleaseMemberCommand(42, 7)));
    }

    public function testDeleteFileRemovesFileOfThisReleaseAndReturnsItsId(): void
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

        $result = $this->handler(DeleteReleaseFileHandler::class, $this->releaseWithFile($file), $privilegesManager, actionsLog: $actionsLog)
            ->handle(new DeleteReleaseFileCommand(42, 7));

        self::assertSame(42, $result->id);
    }

    public function testDeleteFileWithoutFileIdIsBadRequest(): void
    {
        $handler = $this->handler(DeleteReleaseFileHandler::class, $this->releaseWithFile($this->createStub(fileElement::class)), $this->privileges(true));

        $this->assertStatus(400, fn() => $handler->handle(new DeleteReleaseFileCommand(42, 0)));
    }

    public function testDeleteFileOfAnotherElementIsNotFound(): void
    {
        $file = $this->fileWithId(8);
        $file->expects($this->never())->method('deleteElementData');
        $handler = $this->handler(DeleteReleaseFileHandler::class, $this->releaseWithFile($file), $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new DeleteReleaseFileCommand(42, 7)));
    }

    public function testDeleteFileWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $file = $this->fileWithId(7);
        $file->expects($this->never())->method('deleteElementData');
        $handler = $this->handler(DeleteReleaseFileHandler::class, $this->releaseWithFile($file), $this->privileges(false));

        $this->assertStatus(403, fn() => $handler->handle(new DeleteReleaseFileCommand(42, 7)));
    }

    public function testUpdateWritesTheFieldsAndLogsTheChange(): void
    {
        $element = $this->createStub(zxReleaseElement::class);
        $fields = new ReleaseFieldsDto(version: '1.1');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'zxRelease')
            ->willReturn(true);
        $writer = $this->createMock(ReleaseFieldsWriter::class);
        $writer->expects($this->once())->method('apply')->with($element, $fields);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxRelease, 'publicReceive');

        $result = $this->handler(UpdateReleaseHandler::class, $element, $privilegesManager, $actionsLog, writer: $writer)->handle(new UpdateReleaseCommand(42, $fields));

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(ReleaseFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $handler = $this->handler(UpdateReleaseHandler::class, $this->createStub(zxReleaseElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $handler->handle(new UpdateReleaseCommand(42, new ReleaseFieldsDto(version: '1.1'))));
    }

    public function testUpdateOfMissingReleaseIsNotFound(): void
    {
        $handler = $this->handler(UpdateReleaseHandler::class, null, $this->privileges(true));

        $this->assertStatus(404, fn() => $handler->handle(new UpdateReleaseCommand(42, new ReleaseFieldsDto(version: '1.1'))));
    }

    public function testMovingToAnotherProductionNeedsTheRightToAddReleasesThere(): void
    {
        $writer = $this->createMock(ReleaseFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('checkPrivilegesForAction')->willReturnMap([
            [42, 'publicReceive', 'zxRelease', true],
            [9, 'publicAdd', 'zxRelease', false],
        ]);
        $handler = $this->handler(UpdateReleaseHandler::class,
            $this->createStub(zxReleaseElement::class),
            $privilegesManager,
            writer: $writer,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 9 => $this->createStub(zxProdElement::class)],
        );

        $this->assertStatus(403, fn() => $handler->handle(new UpdateReleaseCommand(42, new ReleaseFieldsDto(prodId: 9))));
    }

    public function testCreateNeedsTheRightToAddReleasesToTheProductionAndReturnsTheNewId(): void
    {
        $created = $this->createStub(zxReleaseElement::class);
        $created->method('getId')->willReturn(55);
        $fields = new ReleaseFieldsDto(title: 'Dizzy (crack)', prodId: 9);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(9, 'publicAdd', 'zxRelease')
            ->willReturn(true);
        $creator = $this->createMock(ReleaseCreator::class);
        $creator->expects($this->once())->method('create')->with($fields)->willReturn($created);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($created, StructureType::ZxRelease, 'publicAdd');
        $user = $this->createMock(CurrentUser::class);
        $user->expects($this->once())->method('refreshPrivileges');

        $result = $this->handler(CreateReleaseHandler::class, $this->createStub(zxProdElement::class), $privilegesManager, $actionsLog, creator: $creator, user: $user)->handle(new CreateReleaseCommand($fields));

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutProductionIsBadRequest(): void
    {
        $creator = $this->createMock(ReleaseCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateReleaseHandler::class, null, $this->privileges(true), creator: $creator);

        $this->assertStatus(400, fn() => $handler->handle(new CreateReleaseCommand(new ReleaseFieldsDto(title: 'Dizzy'))));
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(ReleaseCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateReleaseHandler::class, $this->createStub(zxProdElement::class), $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $handler->handle(new CreateReleaseCommand(new ReleaseFieldsDto(prodId: 9))));
    }

    public function testCreateForWhatIsNotAProductionIsBadRequestAndCreatesNothing(): void
    {
        $creator = $this->createMock(ReleaseCreator::class);
        $creator->expects($this->never())->method('create');
        $handler = $this->handler(CreateReleaseHandler::class, $this->createStub(zxReleaseElement::class), $this->privileges(true), creator: $creator);

        $this->assertStatus(400, fn() => $handler->handle(new CreateReleaseCommand(new ReleaseFieldsDto(prodId: 9))));
    }

    public function testSetMemberWritesTheRolesBumpsTheModificationTimeAndLogsIt(): void
    {
        $element = $this->createMock(zxReleaseElement::class);
        $element->expects($this->once())->method('persistModificationTime');
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'publicReceive', 'zxRelease')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Release, ['release', 'graphics'], null, null);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxRelease, 'publicReceive');

        $result = $this->handler(SetReleaseMemberHandler::class,
            null,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
            elementsById: [42 => $element, 7 => $this->createStub(authorElement::class)],
        )->handle(new SetReleaseMemberCommand(42, 7, ['release', 'graphics']));

        self::assertSame(42, $result->id);
    }

    public function testSetMemberTakesAnAuthorAliasAndNoRolesAsUnknown(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Release, ['unknown'], null, null);
        $handler = $this->handler(SetReleaseMemberHandler::class,
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 7 => $this->createStub(authorAliasElement::class)],
        );

        $handler->handle(new SetReleaseMemberCommand(42, 7, []));
    }

    public function testSetMemberOfWhatIsNotAnAuthorIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetReleaseMemberHandler::class,
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 7 => $this->createStub(zxReleaseElement::class)],
        );

        $this->assertStatus(400, fn() => $handler->handle(new SetReleaseMemberCommand(42, 7, ['release', 'graphics'])));
    }

    public function testSetMemberWithARoleOfAnotherKindIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetReleaseMemberHandler::class,
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(400, fn() => $handler->handle(new SetReleaseMemberCommand(42, 7, ['cracker'])));
    }

    public function testSetMemberWithoutThePrivilegeIsForbidden(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $handler = $this->handler(SetReleaseMemberHandler::class,
            null,
            $this->privileges(false),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(403, fn() => $handler->handle(new SetReleaseMemberCommand(42, 7, ['release', 'graphics'])));
    }

    public function testCloneCopiesTheReleaseLogsItAndLetsTheUserEditTheCopy(): void
    {
        $source = $this->createStub(zxReleaseElement::class);
        $clone = $this->createStub(zxReleaseElement::class);
        $clone->method('getId')->willReturn(43);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'clone', 'zxRelease')
            ->willReturn(true);
        $cloner = $this->createMock(ReleaseCloner::class);
        $cloner->expects($this->once())->method('clone')->with($source)->willReturn($clone);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($source, StructureType::ZxRelease, 'clone');
        $user = $this->createMock(CurrentUser::class);
        $user->expects($this->once())->method('refreshPrivileges');

        $result = $this->handler(CloneReleaseHandler::class, $source, $privilegesManager, actionsLog: $actionsLog, user: $user, cloner: $cloner)
            ->handle(new CloneReleaseCommand(42));

        self::assertSame(43, $result->id);
    }

    public function testCloneWithoutThePrivilegeIsForbidden(): void
    {
        $cloner = $this->createMock(ReleaseCloner::class);
        $cloner->expects($this->never())->method('clone');
        $handler = $this->handler(CloneReleaseHandler::class, $this->createStub(zxReleaseElement::class), $this->privileges(false), cloner: $cloner);

        $this->assertStatus(403, fn() => $handler->handle(new CloneReleaseCommand(42)));
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
        ?ReleaseFieldsWriter $writer = null,
        ?ReleaseCreator $creator = null,
        ?CurrentUser $user = null,
        ?array $elementsById = null,
        ?ReleaseCloner $cloner = null,
    ): object {
        $structureManager = $this->createStub(structureManager::class);
        if ($elementsById !== null) {
            $structureManager->method('getElementById')->willReturnCallback(static fn(int $id) => $elementsById[$id] ?? null);
        } else {
            $structureManager->method('getElementById')->willReturn($element);
        }
        $currentUserService = $this->createStub(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($user ?? $this->createStub(CurrentUser::class));

        $actionsLog ??= $this->createStub(ActionsLogService::class);
        $authorshipDbal ??= $this->createStub(AuthorshipDbal::class);
        $transactionRunner = new TransactionRunner($this->createStub(Connection::class));
        $releaseRepository = new ReleaseRepository($structureManager);
        $releasePrivileges = new ReleasePrivileges($privilegesManager, $structureManager);
        $fileImporter = $this->createStub(ElementFileImporter::class);

        foreach ([
            new CreateReleaseHandler($transactionRunner, $releasePrivileges, $creator ?? $this->createStub(ReleaseCreator::class), $actionsLog, $currentUserService),
            new UpdateReleaseHandler($transactionRunner, $releaseRepository, $releasePrivileges, $writer ?? $this->createStub(ReleaseFieldsWriter::class), $this->createStub(ElementAuthorsWriter::class), $this->createStub(ImportOriginsWriter::class), $this->createStub(ElementFileImporter::class), $actionsLog),
            new DeleteReleaseHandler($transactionRunner, $releaseRepository, $releasePrivileges, $actionsLog),
            new CloneReleaseHandler($transactionRunner, $releaseRepository, $releasePrivileges, $cloner ?? $this->createStub(ReleaseCloner::class), $actionsLog, $currentUserService),
            new SetReleaseMemberHandler($transactionRunner, $releaseRepository, $releasePrivileges, $structureManager, $authorshipDbal, $actionsLog),
            new DeleteReleaseMemberHandler($transactionRunner, $releaseRepository, $releasePrivileges, $authorshipDbal, $actionsLog),
            new AddReleaseFilesHandler($transactionRunner, $releaseRepository, $releasePrivileges, $fileImporter, $actionsLog),
            new SetReleaseFileHandler($transactionRunner, $releaseRepository, $releasePrivileges, $fileImporter, $actionsLog),
            new DeleteReleaseFileHandler($transactionRunner, $releaseRepository, $releasePrivileges, $actionsLog),
            $this->createStub(CreateReleasesFromFilesHandler::class),
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

    private function releaseWithFile(fileElement $file): zxReleaseElement
    {
        $element = $this->createStub(zxReleaseElement::class);
        $element->method('getFileSelectorPropertyNames')->willReturn(['screenshotsSelector', 'adFilesSelector']);
        $element->method('getFilesList')->willReturnMap([
            ['screenshotsSelector', []],
            ['adFilesSelector', [$file]],
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
        } catch (ReleaseDataException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
            return;
        }
        self::fail('Expected ReleaseDataException');
    }
}
