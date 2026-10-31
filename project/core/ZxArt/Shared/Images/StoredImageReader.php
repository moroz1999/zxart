<?php

declare(strict_types=1);

namespace ZxArt\Shared\Images;

use fileElement;
use structureManager;
use ZxArt\ZxScreen\ZxScreenRenderer;

/**
 * Reads an image file stored on the site, by the id of its file element. A ZX
 * Spectrum screen comes rendered as the site shows it.
 *
 * @psalm-api
 */
final readonly class StoredImageReader
{
    public function __construct(
        private structureManager $structureManager,
        private ZxScreenRenderer $zxScreenRenderer,
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
        $zxScreenType = $file->getZxScreenType();
        $bytes = $zxScreenType === null ? file_get_contents($path) : $this->zxScreenRenderer->render($path, $zxScreenType);
        if ($bytes === false || $bytes === null) {
            throw new ImageRefusedException("File $fileId cannot be read");
        }

        return ImageBytes::toImage($bytes, "file $fileId");
    }
}
