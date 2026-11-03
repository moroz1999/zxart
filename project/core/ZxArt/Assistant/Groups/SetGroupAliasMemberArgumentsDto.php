<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `set_group_alias_member`.
 */
final readonly class SetGroupAliasMemberArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "aliasId": {"type": "integer", "description": "the group alias"},
                "authorId": {"type": "integer", "description": "an author or an author alias"},
                "roles": {"type": "array", "items": {"type": "string"}, "description": "member roles from list_options; they replace the member's roles, none stands for unknown"},
                "startDate": {"type": "integer", "description": "when the membership started, a Unix timestamp; 0 clears it"},
                "endDate": {"type": "integer", "description": "when it ended, a Unix timestamp; 0 clears it"}
            },
            "required": ["aliasId", "authorId", "roles"],
            "additionalProperties": false
        }
        JSON;

    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $aliasId,
        public int $authorId,
        public array $roles,
        public ?int $startDate = null,
        public ?int $endDate = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            aliasId: $arguments->id('aliasId'),
            authorId: $arguments->id('authorId'),
            roles: $arguments->texts('roles') ?? [],
            startDate: $arguments->number('startDate'),
            endDate: $arguments->number('endDate'),
        );
    }
}
