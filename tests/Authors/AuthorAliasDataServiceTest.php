<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use authorAliasElement;
use authorElement;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Services\AuthorAliasDataService;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Shared\StructureType;

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

    private function createService(
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?AuthorsService $authorsService = null,
    ): AuthorAliasDataService {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturn($element);

        return new AuthorAliasDataService(
            $structureManager,
            $privilegesManager,
            $actionsLog ?? $this->createStub(ActionsLogService::class),
            $authorsService ?? $this->createStub(AuthorsService::class),
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
