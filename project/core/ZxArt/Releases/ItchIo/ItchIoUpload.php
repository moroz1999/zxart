<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

/**
 * A file a game page offers for download. `name` is the label the creator gave
 * it, which is the file name only when they left it at that. A native build is
 * one the creator marked as running on a PC, Mac or Android platform.
 */
readonly class ItchIoUpload
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $nativeBuild,
    ) {
    }
}
