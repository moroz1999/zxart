<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

use RuntimeException;

/**
 * A download that did not happen or was dropped: a refused address, too many
 * redirects, an error status, a file over the size limit, a network failure.
 */
final class DownloadRefusedException extends RuntimeException
{
}
