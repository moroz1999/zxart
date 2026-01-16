<?php

declare(strict_types=1);

namespace ZxArt\Cache\Services;

use App\Paths\PathsManager;
use FilesystemIterator;

class CacheCleanupService
{
    public function __construct(
        private PathsManager $pathsManager,
        private int $inodeThreshold = 200000
    ) {
    }

    public function cleanup(): void
    {
        /** @var mixed $zxCachePath */
        $zxCachePath = $this->pathsManager->getPath('zxCache');
        if (is_string($zxCachePath)) {
            $this->clearInodes($zxCachePath);
        }
        /** @var mixed $imagesCachePath */
        $imagesCachePath = $this->pathsManager->getPath('imagesCache');
        if (is_string($imagesCachePath)) {
            $this->clearInodes($imagesCachePath);
        }
    }

    private function clearInodes(string $path): void
    {
        if (is_dir($path)) {
            $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
            $count = iterator_count($iterator);
            
            foreach ($iterator as $file) {
                if ($count > $this->inodeThreshold) {
                    $count--;
                    unlink((string)$file);
                } else {
                    break;
                }
            }
        }
    }
}
