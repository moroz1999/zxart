<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `update_group_alias`: the alias and the fields to change.
 */
final readonly class UpdateGroupAliasArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "id": {"type": "integer", "description": "the group alias"},
                "groupId": {"type": "integer", "description": "the group the alias belongs to"},
                "title": {"type": "string", "description": "the name"},
                "startDate": {"type": "integer", "description": "when the group started using it, a Unix timestamp; 0 clears it"},
                "endDate": {"type": "integer", "description": "when the group stopped using it, a Unix timestamp; 0 clears it"}
            },
            "required": ["id"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $id,
        public ?int $groupId = null,
        public ?string $title = null,
        public ?int $startDate = null,
        public ?int $endDate = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            id: $arguments->id('id'),
            groupId: $arguments->number('groupId'),
            title: $arguments->text('title'),
            startDate: $arguments->number('startDate'),
            endDate: $arguments->number('endDate'),
        );
    }
}
