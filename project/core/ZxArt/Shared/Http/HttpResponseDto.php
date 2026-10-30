<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

/**
 * How the last response of a request, after its redirects, ended.
 */
final readonly class HttpResponseDto
{
    public function __construct(
        public string $finalUrl,
        public int $status,
        public string $contentType,
        /** The body consumer stopped the transfer before the body ended. */
        public bool $stopped,
    ) {
    }
}
