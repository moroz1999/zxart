<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `remove_group_member`.
 */
final readonly class RemoveGroupMemberArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "groupId": {"type": "integer", "description": "the group"},
                "authorId": {"type": "integer", "description": "the member, an author or an author alias"}
            },
            "required": ["groupId", "authorId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $groupId,
        public int $authorId,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            groupId: $arguments->id('groupId'),
            authorId: $arguments->id('authorId'),
        );
    }
}
