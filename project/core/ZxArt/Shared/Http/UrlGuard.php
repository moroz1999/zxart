<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

/**
 * Decides whether a URL given from outside may be fetched, and where to.
 */
interface UrlGuard
{
    /**
     * @throws UnsafeUrlException
     */
    public function check(string $url): GuardedUrlDto;
}
