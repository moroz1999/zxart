<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use RuntimeException;

/**
 * A request {@see GuardedHttpClient} did not make or could not finish: an
 * address the guard refused, too many redirects, or a transfer that failed.
 */
final class HttpRequestRefusedException extends RuntimeException
{
}
