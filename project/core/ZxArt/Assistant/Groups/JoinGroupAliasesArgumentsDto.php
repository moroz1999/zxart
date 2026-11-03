<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `join_group_aliases`.
 */
final readonly class JoinGroupAliasesArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "mainId": {"type": "integer", "description": "the alias that stays"},
                "joinedId": {"type": "integer", "description": "the duplicate, deleted"}
            },
            "required": ["mainId", "joinedId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $mainId,
        public int $joinedId,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            mainId: $arguments->id('mainId'),
            joinedId: $arguments->id('joinedId'),
        );
    }
}
