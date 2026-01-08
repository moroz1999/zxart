<?php
declare(strict_types=1);

namespace ZxArt\ZxScreen;

class ZxPictureCacheManager
{
    public static function deleteCache(int $id): void
    {
        $directory = 'zximages';
        $cacheDir = PUBLIC_PATH . $directory . '/';

        if (!is_dir($cacheDir)) {
            return;
        }

        $prefix = 'id=' . $id . ';';
        $files = scandir($cacheDir);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (str_starts_with($file, $prefix)) {
                $filePath = $cacheDir . $file;
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }
}
