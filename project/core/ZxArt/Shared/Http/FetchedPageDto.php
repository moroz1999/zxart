<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

/**
 * A page {@see PageFetcher} fetched.
 */
final readonly class FetchedPageDto
{
    public function __construct(
        public string $finalUrl,
        public int $status,
        /** The Content-Type header as it came. */
        public string $contentType,
        /** The text in UTF-8; null for a binary response. */
        public ?string $body,
        /** The body ran past the size limit and was cut there. */
        public bool $truncated,
        /** The bytes received, up to the size limit. */
        public int $size,
    ) {
    }
}
