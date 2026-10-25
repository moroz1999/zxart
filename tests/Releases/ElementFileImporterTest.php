<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use App\Paths\PathsManager;
use fileElement;
use PHPUnit\Framework\TestCase;
use ProdsDownloader;
use structureManager;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Shared\Http\GuardedDownloader;
use zxProdElement;

class ElementFileImporterTest extends TestCase
{
    private string $storedFile;

    protected function setUp(): void
    {
        $this->storedFile = (string)tempnam(sys_get_temp_dir(), 'element-file');
        file_put_contents($this->storedFile, 'stored');
    }

    protected function tearDown(): void
    {
        @unlink($this->storedFile);
    }

    public function testAFileOfTheSameNameAlreadyStoredIsNotDownloadedAgain(): void
    {
        $downloader = $this->createMock(ProdsDownloader::class);
        $downloader->expects($this->never())->method('downloadUrl');
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects($this->never())->method('createElement');

        $this->importer($downloader, $structureManager)->importFile(
            $this->createStub(zxProdElement::class),
            'https://example.org/files/My%20Map.png',
            [$this->storedFile('My Map.png')],
        );
    }

    public function testAFileNotStoredYetIsDownloadedIntoTheUploads(): void
    {
        $downloader = $this->createMock(ProdsDownloader::class);
        $downloader->expects($this->once())
            ->method('downloadUrl')
            ->with('https://example.org/files/My%20Map.png', '/uploads/My Map.png')
            ->willReturn(true);
        $structureManager = $this->createMock(structureManager::class);
        $structureManager->expects($this->once())->method('createElement')->willReturn(null);

        $this->importer($downloader, $structureManager)->importFile(
            $this->createStub(zxProdElement::class),
            'https://example.org/files/My%20Map.png',
            [$this->storedFile('Other Map.png')],
        );
    }

    private function storedFile(string $fileName): fileElement
    {
        $file = $this->createStub(fileElement::class);
        $file->method('__get')->willReturnMap([['fileName', $fileName]]);
        $file->method('getFilePath')->willReturn($this->storedFile);

        return $file;
    }

    private function importer(ProdsDownloader $downloader, structureManager $structureManager): ElementFileImporter
    {
        $pathsManager = $this->createStub(PathsManager::class);
        $pathsManager->method('getPath')->willReturnMap([['uploads', '/uploads/']]);

        return new ElementFileImporter($structureManager, $downloader, $pathsManager, $this->createStub(GuardedDownloader::class), new ReleaseRepository($structureManager));
    }
}
