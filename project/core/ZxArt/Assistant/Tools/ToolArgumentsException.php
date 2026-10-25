<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools;

use RuntimeException;

/**
 * The arguments of a call do not fit the tool's request DTO. The message names
 * the field, so it can go back to the model as the call's error.
 */
final class ToolArgumentsException extends RuntimeException
{
}
