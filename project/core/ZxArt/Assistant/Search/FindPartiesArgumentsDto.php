<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Search;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\PageArguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;

/**
 * The arguments of `find_parties`.
 */
final readonly class FindPartiesArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "phrase": {"type": "string", "description": "a part of the party's title, abbreviation or original name"},
                "year": {"type": "integer", "description": "the year of the party"},
                "limit": {"type": "integer", "description": "the most parties to answer, 20 when not given, up to 100"}
            },
            "required": ["phrase"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public string $phrase,
        public ?int $year,
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
            year: $arguments->number('year'),
            limit: PageArguments::limit($arguments),
        );
    }
}
