<?php

declare(strict_types=1);

namespace ZxArt\Forms;

use RuntimeException;

/**
 * The submitted creation form did not pass the element action's validators, so
 * nothing was created. Distinct from a privilege failure: the request is
 * well-formed but its values are not accepted.
 */
final class FormValidationException extends RuntimeException
{
}
