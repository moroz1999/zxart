<?php

declare(strict_types=1);

namespace ZxArt\Releases\Exception;

use RuntimeException;

/**
 * A file fetched from a URL or uploaded that cannot be stored: the download
 * was refused, or the file is too big or not of a type its place takes.
 */
final class AttachedFileRefusedException extends RuntimeException
{
}
