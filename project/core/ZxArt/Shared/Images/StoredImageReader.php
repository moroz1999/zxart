<?php

declare(strict_types=1);

namespace ZxArt\Shared\Images;

use fileElement;
use structureManager;

/**
 * Reads an image file stored on the site, by the id of its file element.
 *
 * @psalm-api
 */
final readonly class StoredImageReader
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws ImageRefusedException
     */
    public function read(int $fileId): ImageDto
    {
        $file = $this->structureManager->getElementById($fileId);
        if (!$file instanceof fileElement) {
            throw new ImageRefusedException("No file $fileId");
        }
        $path = $file->getFilePath();
        $size = is_file($path) ? filesize($path) : false;
        if ($size === false) {
            throw new ImageRefusedException("File $fileId is not stored here");
        }
        if ($size > ImageBytes::MAX_BYTES) {
            throw new ImageRefusedException("File $fileId is larger than " . ImageBytes::MAX_BYTES . ' bytes');
        }
        $bytes = file_get_contents($path);
        if ($bytes === false) {
            throw new ImageRefusedException("File $fileId cannot be read");
        }

        return ImageBytes::toImage($bytes, "file $fileId");
    }
}
