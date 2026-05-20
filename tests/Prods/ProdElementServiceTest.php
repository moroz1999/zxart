<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\ProdElementService;
use zxProdElement;

#[AllowMockObjectsWithoutExpectations]
class ProdElementServiceTest extends TestCase
{
    private structureManager&MockObject $structureManager;
    private ProdElementService $service;

    protected function setUp(): void
    {
        $this->structureManager = $this->createMock(structureManager::class);
        $this->service = new ProdElementService($this->structureManager);
    }

    public function testGetReturnsElementWhenProdFound(): void
    {
        $element = $this->createMock(zxProdElement::class);
        $this->structureManager->method('getElementById')->with(42)->willReturn($element);

        $result = $this->service->get(42);

        $this->assertSame($element, $result);
    }

    public function testGetThrows404WhenElementNotFound(): void
    {
        $this->structureManager->method('getElementById')->willReturn(null);

        $this->expectException(ProdDetailsException::class);
        $this->expectExceptionMessage('Prod not found');

        $this->service->get(42);
    }

    public function testGetThrows404WhenElementIsWrongType(): void
    {
        // getElementById can return any structureElement; non-zxProdElement must be rejected
        $wrongElement = $this->createMock(\structureElement::class);
        $this->structureManager->method('getElementById')->willReturn($wrongElement);

        $this->expectException(ProdDetailsException::class);
        $this->expectExceptionMessage('Prod not found');

        $this->service->get(42);
    }

    public function testGetThrowsExceptionWithStatus404(): void
    {
        $this->structureManager->method('getElementById')->willReturn(null);

        try {
            $this->service->get(99);
            $this->fail('Expected ProdDetailsException was not thrown');
        } catch (ProdDetailsException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }
}
