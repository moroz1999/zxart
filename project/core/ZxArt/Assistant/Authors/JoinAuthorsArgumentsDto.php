<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `join_authors`.
 */
final readonly class JoinAuthorsArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "mainId": {"type": "integer", "description": "the author that stays"},
                "joinedId": {"type": "integer", "description": "the duplicate, deleted"},
                "asAlias": {"type": ["boolean", "null"], "description": "keep the duplicate's name as an alias of the main author"}
            },
            "required": ["mainId", "joinedId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $mainId,
        public int $joinedId,
        public bool $asAlias = false,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            mainId: $arguments->id('mainId'),
            joinedId: $arguments->id('joinedId'),
            asAlias: $arguments->flag('asAlias'),
        );
    }
}
