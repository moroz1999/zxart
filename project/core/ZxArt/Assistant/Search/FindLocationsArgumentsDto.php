<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Search;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;

/**
 * The arguments of `find_locations`.
 */
final readonly class FindLocationsArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "phrase": {"type": "string", "description": "a part of a country's or a city's name, in any language"}
            },
            "required": ["phrase"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public string $phrase,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        return new self(
            phrase: $arguments->requiredText('phrase'),
        );
    }
}
