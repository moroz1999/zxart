<?php

declare(strict_types=1);

namespace ZxArt\Shared\Images;

use RuntimeException;

/**
 * What was asked for is not an image a model can look at, or cannot be read.
 */
final class ImageRefusedException extends RuntimeException
{
}
