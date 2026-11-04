<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Web;

use ZxArt\Releases\ItchIo\ItchIoUpload;

/**
 * The files an itch.io game offers for download: each upload's id, the label
 * its creator gave it and whether it is a native PC, Mac or Android build.
 */
final readonly class ItchIoFilesDto
{
    /**
     * @param list<ItchIoUpload> $files in page order
     */
    public function __construct(
        public string $gameUrl,
        public array $files,
    ) {
    }
}
