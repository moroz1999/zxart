<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools;

/**
 * A tool that never writes; the assistant calls it freely.
 *
 * @template TRequest of object
 * @extends Tool<TRequest>
 */
interface ReadTool extends Tool
{
}
