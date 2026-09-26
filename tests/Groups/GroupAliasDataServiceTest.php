<?php

declare(strict_types=1);

namespace ZxArt\Tests\Groups;

use groupAliasElement;
use groupElement;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Services\GroupAliasDataService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Shared\StructureType;

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

    private function createService(
        ?structureElement $element,
        privilegesManager $privilegesManager,
        ?ActionsLogService $actionsLog = null,
        ?GroupsService $groupsService = null,
    ): GroupAliasDataService {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturn($element);

        return new GroupAliasDataService(
            $structureManager,
            $privilegesManager,
            $actionsLog ?? $this->createStub(ActionsLogService::class),
            $groupsService ?? $this->createStub(GroupsService::class),
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
