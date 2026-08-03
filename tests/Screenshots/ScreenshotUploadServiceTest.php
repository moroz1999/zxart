<?php

declare(strict_types=1);

namespace ZxArt\Tests\Screenshots;

use linksManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureManager;
use ZxArt\Prods\Dto\ProdFilesDto;
use ZxArt\Prods\ProdMediaService;
use ZxArt\Screenshots\Exception\ScreenshotUploadException;
use ZxArt\Screenshots\ScreenshotUploadService;
use zxProdElement;
use zxReleaseElement;

#[AllowMockObjectsWithoutExpectations]
class ScreenshotUploadServiceTest extends TestCase
{
    private structureManager&MockObject $structureManager;
    private linksManager&MockObject $linksManager;
    private ProdMediaService&MockObject $prodMediaService;
    private ScreenshotUploadService $service;

    protected function setUp(): void
    {
        $this->structureManager = $this->createMock(structureManager::class);
        $this->linksManager = $this->createMock(linksManager::class);
        $this->prodMediaService = $this->createMock(ProdMediaService::class);
        $this->service = new ScreenshotUploadService(
            $this->structureManager,
            $this->linksManager,
            $this->prodMediaService,
        );
    }

    public function testUploadRunsProdActionAndReturnsProdScreenshots(): void
    {
        $files = new ProdFilesDto(files: []);
        $prod = $this->createMock(zxProdElement::class);
        $prod->expects($this->once())->method('executeAction')->with('uploadScreenshot')->willReturn(true);
        $this->structureManager->method('getElementById')->with(42)->willReturn($prod);
        $this->prodMediaService->expects($this->once())->method('getProdScreenshots')->with(42)->willReturn($files);

        $this->assertSame($files, $this->service->upload(42));
    }

    public function testUploadRunsReleaseActionAndReturnsReleaseScreenshots(): void
    {
        $files = new ProdFilesDto(files: []);
        $release = $this->createMock(zxReleaseElement::class);
        $release->expects($this->once())->method('executeAction')->with('uploadScreenshot')->willReturn(true);
        $this->structureManager->method('getElementById')->with(7)->willReturn($release);
        $this->prodMediaService->expects($this->once())->method('getReleaseScreenshots')->with(7)->willReturn($files);

        $this->assertSame($files, $this->service->upload(7));
    }

    public function testUploadThrows404WhenElementIsNotProdOrRelease(): void
    {
        $this->structureManager->method('getElementById')->willReturn(null);

        $this->expectException(ScreenshotUploadException::class);
        $this->expectExceptionMessage('Element not found');

        $this->service->upload(42);
    }

    public function testUploadThrows403WhenActionIsNotAllowed(): void
    {
        $prod = $this->createMock(zxProdElement::class);
        $prod->method('executeAction')->willReturn(false);
        $this->structureManager->method('getElementById')->willReturn($prod);

        $this->expectException(ScreenshotUploadException::class);
        $this->expectExceptionMessage('Access denied');

        $this->service->upload(42);
    }
}
