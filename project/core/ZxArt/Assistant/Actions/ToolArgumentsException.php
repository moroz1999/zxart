<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Actions;

use RuntimeException;

/**
 * The arguments of a call do not fit the action's schema. The message names
 * the field, so it can go back to the model as the call's error.
 */
final class ToolArgumentsException extends RuntimeException
{
}
