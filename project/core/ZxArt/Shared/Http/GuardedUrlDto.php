<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

/**
 * A URL that may be fetched, with the checked address the request must go to
 * so that a second lookup cannot lead it elsewhere.
 */
final readonly class GuardedUrlDto
{
    public function __construct(
        public string $url,
        public string $host,
        public int $port,
        public string $address,
    ) {
    }
}
