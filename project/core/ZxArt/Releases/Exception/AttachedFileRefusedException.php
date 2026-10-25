<?php

declare(strict_types=1);

namespace ZxArt\Releases\Exception;

use RuntimeException;

/**
 * A file fetched from a URL that cannot be stored: the download was refused,
 * or the file is not of a type its list takes.
 */
final class AttachedFileRefusedException extends RuntimeException
{
}
