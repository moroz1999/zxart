<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `update_author_alias`: the alias and the fields to change.
 */
final readonly class UpdateAuthorAliasArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "id": {"type": "integer", "description": "the author alias"},
                "authorId": {"type": ["integer", "null"], "description": "the author the alias belongs to"},
                "title": {"type": ["string", "null"], "description": "the name"},
                "startDate": {"type": ["integer", "null"], "description": "when the author started using it, a Unix timestamp; 0 clears it"},
                "endDate": {"type": ["integer", "null"], "description": "when the author stopped using it, a Unix timestamp; 0 clears it"}
            },
            "required": ["id"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $id,
        public ?int $authorId = null,
        public ?string $title = null,
        public ?int $startDate = null,
        public ?int $endDate = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            id: $arguments->id('id'),
            authorId: $arguments->number('authorId'),
            title: $arguments->text('title'),
            startDate: $arguments->number('startDate'),
            endDate: $arguments->number('endDate'),
        );
    }
}
