<?php

declare(strict_types=1);

namespace ZxArt\Tests\Cache;

use App\Paths\PathsManager;
use PHPUnit\Framework\TestCase;
use ZxArt\Cache\Services\CacheCleanupService;

class CacheCleanupServiceTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zxart_test_' . uniqid('', true);
        mkdir($this->tempDir);
        mkdir($this->tempDir . DIRECTORY_SEPARATOR . 'zxCache');
        mkdir($this->tempDir . DIRECTORY_SEPARATOR . 'imagesCache');
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tempDir);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            (is_dir($path)) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testCleanupRemovesFilesOverThreshold(): void
    {
        $zxCachePath = $this->tempDir . DIRECTORY_SEPARATOR . 'zxCache';
        $imagesCachePath = $this->tempDir . DIRECTORY_SEPARATOR . 'imagesCache';

        for ($i = 0; $i < 5; $i++) {
            touch($zxCachePath . DIRECTORY_SEPARATOR . 'file' . $i);
            touch($imagesCachePath . DIRECTORY_SEPARATOR . 'file' . $i);
        }

        $pathsManagerMock = $this->createMock(PathsManager::class);
        $pathsManagerMock->method('getPath')
            ->willReturnMap([
                ['zxCache', $zxCachePath],
                ['imagesCache', $imagesCachePath],
            ]);

        $service = new CacheCleanupService($pathsManagerMock, 3);
        $service->cleanup();

        $this->assertEquals(3, iterator_count(new \FilesystemIterator($zxCachePath, \FilesystemIterator::SKIP_DOTS)));
        $this->assertEquals(3, iterator_count(new \FilesystemIterator($imagesCachePath, \FilesystemIterator::SKIP_DOTS)));
    }
}
