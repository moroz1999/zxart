<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `remove_group_alias_member`.
 */
final readonly class RemoveGroupAliasMemberArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "aliasId": {"type": "integer", "description": "the group alias"},
                "authorId": {"type": "integer", "description": "the member, an author or an author alias"}
            },
            "required": ["aliasId", "authorId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $aliasId,
        public int $authorId,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            aliasId: $arguments->id('aliasId'),
            authorId: $arguments->id('authorId'),
        );
    }
}
