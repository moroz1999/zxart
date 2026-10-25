<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use Illuminate\Database\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Releases\Services\ReleaseCloner;
use ZxArt\Releases\Services\ReleaseCreator;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Releases\Services\ReleaseFieldsWriter;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Releases\UseCases\AddReleaseFilesHandler;
use ZxArt\Releases\UseCases\CloneReleaseHandler;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Releases\UseCases\CreateReleasesFromFilesHandler;
use ZxArt\Releases\UseCases\DeleteReleaseFileHandler;
use ZxArt\Releases\UseCases\DeleteReleaseHandler;
use ZxArt\Releases\UseCases\DeleteReleaseMemberHandler;
use ZxArt\Releases\UseCases\SetReleaseFileHandler;
use ZxArt\Releases\UseCases\SetReleaseMemberHandler;
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

final class ReleaseDataServiceTest extends TestCase
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
        $other = $this->createMock(zxProdElement::class);
        $other->expects($this->never())->method('deleteElementData');
        $service = $this->createService($other, $this->privileges(true));

        $this->assertStatus(404, fn() => $service->delete(42));
    }

    public function testDeleteWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $element = $this->createMock(zxReleaseElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $service = $this->createService($element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $service->delete(42));
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
        $authorship->expects($this->once())
            ->method('deleteAuthorship')
            ->with(42, 7, EntityType::Release)
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxRelease, 'deleteAuthor');

        $result = $this->createService(
            $element,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
        )->deleteMember(42, 7);

        self::assertSame(42, $result->id);
    }

    public function testDeleteMemberWithoutAuthorIdIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $service = $this->createService(
            $this->createStub(zxReleaseElement::class),
            $this->privileges(true),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(400, fn() => $service->deleteMember(42, 0));
    }

    public function testDeleteMemberOfAuthorWhoIsNotAMemberIsNotFound(): void
    {
        // /ajax/ answered success for a removal that changed nothing
        $authorship = $this->createStub(AuthorshipDbal::class);
        $authorship->method('deleteAuthorship')->willReturn(false);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $service = $this->createService(
            $this->createStub(zxReleaseElement::class),
            $this->privileges(true),
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
        );

        $this->assertStatus(404, fn() => $service->deleteMember(42, 7));
    }

    public function testDeleteMemberWithoutPrivilegeIsForbiddenAndRemovesNothing(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $service = $this->createService(
            $this->createStub(zxReleaseElement::class),
            $this->privileges(false),
            authorshipDbal: $authorship,
        );

        $this->assertStatus(403, fn() => $service->deleteMember(42, 7));
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

        $result = $this->createService($this->releaseWithFile($file), $privilegesManager, actionsLog: $actionsLog)
            ->deleteFile(42, 7);

        self::assertSame(42, $result->id);
    }

    public function testDeleteFileWithoutFileIdIsBadRequest(): void
    {
        $service = $this->createService($this->releaseWithFile($this->createStub(fileElement::class)), $this->privileges(true));

        $this->assertStatus(400, fn() => $service->deleteFile(42, 0));
    }

    public function testDeleteFileOfAnotherElementIsNotFound(): void
    {
        $file = $this->fileWithId(8);
        $file->expects($this->never())->method('deleteElementData');
        $service = $this->createService($this->releaseWithFile($file), $this->privileges(true));

        $this->assertStatus(404, fn() => $service->deleteFile(42, 7));
    }

    public function testDeleteFileWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $file = $this->fileWithId(7);
        $file->expects($this->never())->method('deleteElementData');
        $service = $this->createService($this->releaseWithFile($file), $this->privileges(false));

        $this->assertStatus(403, fn() => $service->deleteFile(42, 7));
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

        $result = $this->createService($element, $privilegesManager, $actionsLog, writer: $writer)->update(42, $fields);

        self::assertSame(42, $result->id);
    }

    public function testUpdateWithoutPrivilegeIsForbiddenAndWritesNothing(): void
    {
        $writer = $this->createMock(ReleaseFieldsWriter::class);
        $writer->expects($this->never())->method('apply');
        $service = $this->createService($this->createStub(zxReleaseElement::class), $this->privileges(false), writer: $writer);

        $this->assertStatus(403, fn() => $service->update(42, new ReleaseFieldsDto(version: '1.1')));
    }

    public function testUpdateOfMissingReleaseIsNotFound(): void
    {
        $service = $this->createService(null, $this->privileges(true));

        $this->assertStatus(404, fn() => $service->update(42, new ReleaseFieldsDto(version: '1.1')));
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
        $service = $this->createService(
            $this->createStub(zxReleaseElement::class),
            $privilegesManager,
            writer: $writer,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 9 => $this->createStub(zxProdElement::class)],
        );

        $this->assertStatus(403, fn() => $service->update(42, new ReleaseFieldsDto(prodId: 9)));
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

        $result = $this->createService($this->createStub(zxProdElement::class), $privilegesManager, $actionsLog, creator: $creator, user: $user)->create($fields);

        self::assertSame(55, $result->id);
    }

    public function testCreateWithoutProductionIsBadRequest(): void
    {
        $creator = $this->createMock(ReleaseCreator::class);
        $creator->expects($this->never())->method('create');
        $service = $this->createService(null, $this->privileges(true), creator: $creator);

        $this->assertStatus(400, fn() => $service->create(new ReleaseFieldsDto(title: 'Dizzy')));
    }

    public function testCreateWithoutPrivilegeIsForbiddenAndCreatesNothing(): void
    {
        $creator = $this->createMock(ReleaseCreator::class);
        $creator->expects($this->never())->method('create');
        $service = $this->createService($this->createStub(zxProdElement::class), $this->privileges(false), creator: $creator);

        $this->assertStatus(403, fn() => $service->create(new ReleaseFieldsDto(prodId: 9)));
    }

    public function testCreateForWhatIsNotAProductionIsBadRequestAndCreatesNothing(): void
    {
        $creator = $this->createMock(ReleaseCreator::class);
        $creator->expects($this->never())->method('create');
        $service = $this->createService($this->createStub(zxReleaseElement::class), $this->privileges(true), creator: $creator);

        $this->assertStatus(400, fn() => $service->create(new ReleaseFieldsDto(prodId: 9)));
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

        $result = $this->createService(
            null,
            $privilegesManager,
            actionsLog: $actionsLog,
            authorshipDbal: $authorship,
            elementsById: [42 => $element, 7 => $this->createStub(authorElement::class)],
        )->setMember(42, 7, ['release', 'graphics']);

        self::assertSame(42, $result->id);
    }

    public function testSetMemberTakesAnAuthorAliasAndNoRolesAsUnknown(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->once())
            ->method('setAuthorship')
            ->with(42, 7, EntityType::Release, ['unknown'], null, null);
        $service = $this->createService(
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 7 => $this->createStub(authorAliasElement::class)],
        );

        $service->setMember(42, 7, []);
    }

    public function testSetMemberOfWhatIsNotAnAuthorIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $service = $this->createService(
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 7 => $this->createStub(zxReleaseElement::class)],
        );

        $this->assertStatus(400, fn() => $service->setMember(42, 7, ['release', 'graphics']));
    }

    public function testSetMemberWithARoleOfAnotherKindIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $service = $this->createService(
            null,
            $this->privileges(true),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(400, fn() => $service->setMember(42, 7, ['cracker']));
    }

    public function testSetMemberWithoutThePrivilegeIsForbidden(): void
    {
        $authorship = $this->createMock(AuthorshipDbal::class);
        $authorship->expects($this->never())->method('setAuthorship');
        $service = $this->createService(
            null,
            $this->privileges(false),
            authorshipDbal: $authorship,
            elementsById: [42 => $this->createStub(zxReleaseElement::class), 7 => $this->createStub(authorElement::class)],
        );

        $this->assertStatus(403, fn() => $service->setMember(42, 7, ['release', 'graphics']));
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

        $result = $this->createService($source, $privilegesManager, actionsLog: $actionsLog, user: $user, cloner: $cloner)
            ->cloneRelease(42);

        self::assertSame(43, $result->id);
    }

    public function testCloneWithoutThePrivilegeIsForbidden(): void
    {
        $cloner = $this->createMock(ReleaseCloner::class);
        $cloner->expects($this->never())->method('clone');
        $service = $this->createService($this->createStub(zxReleaseElement::class), $this->privileges(false), cloner: $cloner);

        $this->assertStatus(403, fn() => $service->cloneRelease(42));
    }

    private function createService(
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?AuthorshipDbal $authorshipDbal = null,
        ?ReleaseFieldsWriter $writer = null,
        ?ReleaseCreator $creator = null,
        ?CurrentUser $user = null,
        ?array $elementsById = null,
        ?ReleaseCloner $cloner = null,
    ): ReleaseDataService {
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

        return new ReleaseDataService(
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
        );
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
