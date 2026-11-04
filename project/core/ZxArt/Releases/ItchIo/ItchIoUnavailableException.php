<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

use RuntimeException;

/**
 * itch.io did not give what a download needs: the game page, its list of
 * files or the address of a file.
 */
final class ItchIoUnavailableException extends RuntimeException
{
}
