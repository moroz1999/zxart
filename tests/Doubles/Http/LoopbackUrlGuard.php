<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Http;

use ZxArt\Shared\Http\GuardedUrlDto;
use ZxArt\Shared\Http\UnsafeUrlException;
use ZxArt\Shared\Http\UrlGuard;

/**
 * Lets only {@see self::HOST} through, pinned to the loopback address the
 * {@see LocalFileServer} listens on; refuses every other host.
 */
final class LoopbackUrlGuard implements UrlGuard
{
    public const string HOST = 'files.test';

    public function check(string $url): GuardedUrlDto
    {
        $host = (string)parse_url($url, PHP_URL_HOST);
        if ($host !== self::HOST) {
            throw new UnsafeUrlException("Refused: $url");
        }

        return new GuardedUrlDto($url, $host, (int)parse_url($url, PHP_URL_PORT), '127.0.0.1');
    }
}
