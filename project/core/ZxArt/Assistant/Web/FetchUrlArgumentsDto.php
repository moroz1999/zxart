<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Web;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;

/**
 * The arguments of `fetch_url`.
 */
final readonly class FetchUrlArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "url": {"type": "string", "description": "an http or https address"}
            },
            "required": ["url"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public string $url,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        return new self(
            url: $arguments->requiredText('url'),
        );
    }
}
