<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `create_group_alias`.
 */
final readonly class CreateGroupAliasArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "groupId": {"type": "integer", "description": "the group the alias belongs to"},
                "title": {"type": "string", "description": "the name"},
                "startDate": {"type": ["integer", "null"], "description": "when the group started using it, a Unix timestamp; 0 clears it"},
                "endDate": {"type": ["integer", "null"], "description": "when the group stopped using it, a Unix timestamp; 0 clears it"}
            },
            "required": ["groupId", "title"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $groupId,
        public string $title,
        public ?int $startDate = null,
        public ?int $endDate = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            groupId: $arguments->id('groupId'),
            title: $arguments->requiredText('title'),
            startDate: $arguments->number('startDate'),
            endDate: $arguments->number('endDate'),
        );
    }
}
