<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Catalogues;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\PageArguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;

/**
 * The arguments of `find_tags`.
 */
final readonly class FindTagsArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "phrase": {"type": "string", "description": "a part of the tag's name or of a synonym, in any language"},
                "limit": {"type": ["integer", "null"], "description": "the most tags to answer, 20 when not given, up to 100"}
            },
            "required": ["phrase"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public string $phrase,
        public int $limit,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        return new self(
            phrase: $arguments->requiredText('phrase'),
            limit: PageArguments::limit($arguments),
        );
    }
}
