<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

use RuntimeException;

/**
 * A field of an entity write names something that does not exist or cannot
 * stand there. The entity's own service reports it as a bad request.
 */
final class InvalidReferenceException extends RuntimeException
{
}
