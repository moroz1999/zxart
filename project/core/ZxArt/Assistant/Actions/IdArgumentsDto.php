<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Actions;

/**
 * The arguments of an action on one entity: its id.
 */
final readonly class IdArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "id": {"type": "integer", "description": "the id of the entity"}
            },
            "required": ["id"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $id,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            id: $arguments->id('id'),
        );
    }
}
