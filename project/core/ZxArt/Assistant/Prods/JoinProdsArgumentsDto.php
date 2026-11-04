<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `join_prods`.
 */
final readonly class JoinProdsArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "mainId": {"type": "integer", "description": "the production that stays"},
                "joinedId": {"type": "integer", "description": "the duplicate, deleted"},
                "releasesOnly": {"type": ["boolean", "null"], "description": "move only the releases and materials, not the authors, groups, publishers, categories and fields"}
            },
            "required": ["mainId", "joinedId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $mainId,
        public int $joinedId,
        public bool $releasesOnly = false,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            mainId: $arguments->id('mainId'),
            joinedId: $arguments->id('joinedId'),
            releasesOnly: $arguments->flag('releasesOnly'),
        );
    }
}
