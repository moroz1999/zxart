<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `create_author_alias`.
 */
final readonly class CreateAuthorAliasArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "authorId": {"type": "integer", "description": "the author the alias belongs to"},
                "title": {"type": "string", "description": "the name"},
                "startDate": {"type": ["integer", "null"], "description": "when the author started using it, a Unix timestamp; 0 clears it"},
                "endDate": {"type": ["integer", "null"], "description": "when the author stopped using it, a Unix timestamp; 0 clears it"}
            },
            "required": ["authorId", "title"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $authorId,
        public string $title,
        public ?int $startDate = null,
        public ?int $endDate = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            authorId: $arguments->id('authorId'),
            title: $arguments->requiredText('title'),
            startDate: $arguments->number('startDate'),
            endDate: $arguments->number('endDate'),
        );
    }
}
