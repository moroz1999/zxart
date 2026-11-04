<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Search;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\PageArguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;

/**
 * The arguments of `search`: a phrase, the types to search, how many to answer.
 */
final readonly class SearchArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "phrase": {"type": "string", "description": "a part of the title, or of an author's real name"},
                "types": {"type": ["array", "null"], "items": {"type": "string", "enum": ["prod", "release", "author", "authorAlias", "group", "groupAlias"]}, "description": "the types to search; all six when not given"},
                "limit": {"type": ["integer", "null"], "description": "the most results to answer, 20 when not given, up to 100"}
            },
            "required": ["phrase"],
            "additionalProperties": false
        }
        JSON;

    /**
     * @param list<SearchableType> $types the types to search; empty for all six
     */
    public function __construct(
        public string $phrase,
        public array $types,
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
            types: array_map(
                static fn(string $type): SearchableType => SearchableType::from($type),
                $arguments->textsOf('types', array_column(SearchableType::cases(), 'value')) ?? [],
            ),
            limit: PageArguments::limit($arguments),
        );
    }
}
