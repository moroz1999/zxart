<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use RuntimeException;

/**
 * A URL the site does not fetch: not http(s), or leading to an address inside
 * a private network.
 */
final class UnsafeUrlException extends RuntimeException
{
}
