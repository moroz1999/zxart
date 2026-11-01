<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Agent;

use RuntimeException;

/**
 * A proposed plan that cannot be offered to the user; the message tells the
 * model why.
 */
final class PlanRefusedException extends RuntimeException
{
}
