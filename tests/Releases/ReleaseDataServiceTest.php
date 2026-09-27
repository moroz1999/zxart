<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use fileElement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
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
        $element = $this->createStub(zxReleaseElement::class);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'deleteAuthor', 'zxRelease')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipRepository::class);
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
            authorshipRepository: $authorship,
        )->deleteMember(42, 7);

        self::assertSame(42, $result->id);
    }

    public function testDeleteMemberWithoutAuthorIdIsBadRequest(): void
    {
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->never())->method('deleteAuthorship');
        $service = $this->createService(
            $this->createStub(zxReleaseElement::class),
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
            $this->createStub(zxReleaseElement::class),
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
            $this->createStub(zxReleaseElement::class),
            $this->privileges(false),
            authorshipRepository: $authorship,
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

    private function createService(
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?AuthorshipRepository $authorshipRepository = null,
    ): ReleaseDataService {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturn($element);

        return new ReleaseDataService(
            $structureManager,
            $privilegesManager,
            $actionsLog ?? $this->createStub(ActionsLogService::class),
            $authorshipRepository ?? $this->createStub(AuthorshipRepository::class),
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
