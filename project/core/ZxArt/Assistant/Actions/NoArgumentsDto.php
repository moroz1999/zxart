<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Actions;

/**
 * The schema of an action that takes no arguments.
 */
final readonly class NoArgumentsDto
{
    public const string SCHEMA = '{"type": "object", "properties": {}, "additionalProperties": false}';
}
