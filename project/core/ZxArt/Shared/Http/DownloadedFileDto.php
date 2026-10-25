<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

/**
 * A file the downloader stored.
 *
 * @psalm-api
 */
final readonly class DownloadedFileDto
{
    /**
     * @param string $contentType detected from the stored bytes, not taken from the response
     */
    public function __construct(
        public string $finalUrl,
        public string $contentType,
        public int $size,
    ) {
    }
}
