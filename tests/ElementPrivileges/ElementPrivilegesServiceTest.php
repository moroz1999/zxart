<?php

declare(strict_types=1);

namespace ZxArt\Tests\ElementPrivileges;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\ElementPrivileges\ElementPrivilegesService;
use ZxArt\Tags\Exception\TagsException;

#[AllowMockObjectsWithoutExpectations]
class ElementPrivilegesServiceTest extends TestCase
{
    private structureManager&MockObject $structureManager;
    private privilegesManager&MockObject $privilegesManager;
    private ElementPrivilegesService $service;

    protected function setUp(): void
    {
        $this->structureManager = $this->createMock(structureManager::class);
        $this->privilegesManager = $this->createMock(privilegesManager::class);
        $this->service = new ElementPrivilegesService($this->structureManager, $this->privilegesManager);
    }

    public function testGetPrivilegesReturnsBooleanMapForRequestedPrivileges(): void
    {
        $element = new TestPrivilegeStructureElement('zxPicture');
        $this->structureManager->method('getElementById')->with(42)->willReturn($element);

        $this->privilegesManager->method('checkPrivilegesForAction')
            ->willReturnCallback(
                static fn(int $elementId, string $action, string $structureType): bool => $elementId === 42
                    && $structureType === 'zxPicture'
                    && $action === 'submitTags'
            );

        $result = $this->service->getPrivileges(42, [' submitTags ', '', 'publicDelete']);

        $this->assertSame(42, $result->elementId);
        $this->assertSame(
            [
                'submitTags' => true,
                'publicDelete' => false,
            ],
            $result->privileges,
        );
    }

    public function testGetPrivilegesThrowsWhenElementDoesNotExist(): void
    {
        $this->structureManager->method('getElementById')->with(42)->willReturn(null);

        $this->expectException(TagsException::class);
        $this->expectExceptionMessage('Element not found');

        $this->service->getPrivileges(42, ['submitTags']);
    }
}

final class TestPrivilegeStructureElement extends structureElement
{
    public function __construct(
        public string $structureType,
    ) {
    }

    protected function setModuleStructure(&$moduleStructure): void
    {
        $moduleStructure = [];
    }
}
