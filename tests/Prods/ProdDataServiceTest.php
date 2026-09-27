<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use authorElement;
use fileElement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use zxProdElement;

final class ProdDataServiceTest extends TestCase
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
        $element = $this->createMock(zxProdElement::class);
        $element->expects($this->never())->method('deleteElementData');
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->never())->method('log');
        $service = $this->createService($element, $this->privileges(false), $actionsLog);

        $this->assertStatus(403, fn() => $service->delete(42));
    }

    public function testDeleteMemberRemovesOnlyThisProdAuthorshipAndReturnsItsId(): void
    {
        $element = $this->createStub(zxProdElement::class);
        $privilegesManager = $this->createMock(privilegesManager::class);
        $privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'deleteAuthor', 'zxProd')
            ->willReturn(true);
        $authorship = $this->createMock(AuthorshipRepository::class);
        $authorship->expects($this->once())
            ->method('deleteAuthorship')
            ->with(42, 7, EntityType::Prod)
            ->willReturn(true);
        $actionsLog = $this->createMock(ActionsLogService::class);
        $actionsLog->expects($this->once())->method('log')->with($element, StructureType::ZxProd, 'deleteAuthor');

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
            $this->createStub(zxProdElement::class),
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
            $this->createStub(zxProdElement::class),
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
            $this->createStub(zxProdElement::class),
            $this->privileges(false),
            authorshipRepository: $authorship,
        );

        $this->assertStatus(403, fn() => $service->deleteMember(42, 7));
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

        $result = $this->createService($this->prodWithFile($file), $privilegesManager, actionsLog: $actionsLog)
            ->deleteFile(42, 7);

        self::assertSame(42, $result->id);
    }

    public function testDeleteFileWithoutFileIdIsBadRequest(): void
    {
        $service = $this->createService($this->prodWithFile($this->createStub(fileElement::class)), $this->privileges(true));

        $this->assertStatus(400, fn() => $service->deleteFile(42, 0));
    }

    public function testDeleteFileOfAnotherElementIsNotFound(): void
    {
        $file = $this->fileWithId(8);
        $file->expects($this->never())->method('deleteElementData');
        $service = $this->createService($this->prodWithFile($file), $this->privileges(true));

        $this->assertStatus(404, fn() => $service->deleteFile(42, 7));
    }

    public function testDeleteFileWithoutPrivilegeIsForbiddenAndDeletesNothing(): void
    {
        $file = $this->fileWithId(7);
        $file->expects($this->never())->method('deleteElementData');
        $service = $this->createService($this->prodWithFile($file), $this->privileges(false));

        $this->assertStatus(403, fn() => $service->deleteFile(42, 7));
    }

    private function createService(
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?AuthorshipRepository $authorshipRepository = null,
    ): ProdDataService {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturn($element);

        return new ProdDataService(
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
