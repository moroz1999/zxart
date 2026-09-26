<?php

declare(strict_types=1);

namespace ZxArt\Tests\EntityConversion;

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\EntityConversion\ConversionTarget;
use ZxArt\EntityConversion\Exception\EntityConversionException;
use ZxArt\EntityConversion\Services\EntityConversionService;
use ZxArt\Groups\Services\GroupsService;

final class EntityConversionServiceTest extends TestCase
{
    public function testAuthorAliasConvertsToAuthor(): void
    {
        $alias = $this->createStub(authorAliasElement::class);
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->once())
            ->method('convertAliasToAuthor')
            ->with($alias)
            ->willReturn($this->elementWithId(authorElement::class, 900));
        $service = $this->createService($alias, true, $authorsService, $this->createStub(GroupsService::class));

        self::assertSame(900, $service->convert(42, ConversionTarget::Author)->id);
    }

    public function testAuthorConvertsToGroup(): void
    {
        $author = $this->createStub(authorElement::class);
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->once())
            ->method('convertAuthorToGroup')
            ->with($author)
            ->willReturn($this->elementWithId(groupElement::class, 901));
        $service = $this->createService($author, true, $this->createStub(AuthorsService::class), $groupsService);

        self::assertSame(901, $service->convert(42, ConversionTarget::Group)->id);
    }

    public function testGroupConvertsToAuthor(): void
    {
        $group = $this->createStub(groupElement::class);
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->once())
            ->method('convertGroupToAuthor')
            ->with($group)
            ->willReturn($this->elementWithId(authorElement::class, 902));
        $service = $this->createService($group, true, $authorsService, $this->createStub(GroupsService::class));

        self::assertSame(902, $service->convert(42, ConversionTarget::Author)->id);
    }

    public function testGroupAliasConvertsToGroup(): void
    {
        $alias = $this->createStub(groupAliasElement::class);
        $groupsService = $this->createMock(GroupsService::class);
        $groupsService->expects($this->once())
            ->method('convertGroupAliasToGroup')
            ->with($alias)
            ->willReturn($this->elementWithId(groupElement::class, 903));
        $service = $this->createService($alias, true, $this->createStub(AuthorsService::class), $groupsService);

        self::assertSame(903, $service->convert(42, ConversionTarget::Group)->id);
    }

    public function testConvertWithoutIdIsBadRequest(): void
    {
        $service = $this->createService(null, true, $this->createStub(AuthorsService::class), $this->createStub(GroupsService::class));

        $this->assertStatus(400, fn() => $service->convert(0, ConversionTarget::Author));
    }

    public function testConvertOfMissingElementIsNotFound(): void
    {
        $service = $this->createService(null, true, $this->createStub(AuthorsService::class), $this->createStub(GroupsService::class));

        $this->assertStatus(404, fn() => $service->convert(42, ConversionTarget::Author));
    }

    public function testUnsupportedConversionIsBadRequest(): void
    {
        $service = $this->createService(
            $this->createStub(authorElement::class),
            true,
            $this->createStub(AuthorsService::class),
            $this->createStub(GroupsService::class),
        );

        $this->assertStatus(400, fn() => $service->convert(42, ConversionTarget::Author));
    }

    public function testConvertWithoutPrivilegeIsForbiddenAndConvertsNothing(): void
    {
        $authorsService = $this->createMock(AuthorsService::class);
        $authorsService->expects($this->never())->method('convertAliasToAuthor');
        $service = $this->createService(
            $this->createStub(authorAliasElement::class),
            false,
            $authorsService,
            $this->createStub(GroupsService::class),
        );

        $this->assertStatus(403, fn() => $service->convert(42, ConversionTarget::Author));
    }

    public function testFailedConversionIsServerError(): void
    {
        $groupsService = $this->createStub(GroupsService::class);
        $groupsService->method('convertGroupAliasToGroup')->willReturn(null);
        $service = $this->createService(
            $this->createStub(groupAliasElement::class),
            true,
            $this->createStub(AuthorsService::class),
            $groupsService,
        );

        $this->assertStatus(500, fn() => $service->convert(42, ConversionTarget::Group));
    }

    private function createService(
        ?structureElement $element,
        bool $isAllowed,
        AuthorsService $authorsService,
        GroupsService $groupsService,
    ): EntityConversionService {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturn($element);
        $privilegesManager = $this->createStub(privilegesManager::class);
        $privilegesManager->method('checkPrivilegesForAction')->willReturn($isAllowed);

        return new EntityConversionService($structureManager, $privilegesManager, $authorsService, $groupsService);
    }

    /**
     * @template T of structureElement
     * @param class-string<T> $className
     * @return T
     */
    private function elementWithId(string $className, int $id): structureElement
    {
        $element = $this->createStub($className);
        $element->method('getId')->willReturn($id);

        return $element;
    }

    private function assertStatus(int $statusCode, callable $call): void
    {
        try {
            $call();
        } catch (EntityConversionException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
            return;
        }
        self::fail('Expected EntityConversionException');
    }
}
