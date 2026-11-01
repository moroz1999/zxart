<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Llm;

use RuntimeException;

/**
 * The model could not be asked or gave no usable answer.
 */
final class LlmException extends RuntimeException
{
}
