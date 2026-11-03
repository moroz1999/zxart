<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `set_release_author`.
 */
final readonly class SetReleaseAuthorArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "releaseId": {"type": "integer", "description": "the release"},
                "authorId": {"type": "integer", "description": "an author or an author alias"},
                "roles": {"type": "array", "items": {"type": "string"}, "description": "roles from list_options; they replace the author's roles, none stands for unknown"}
            },
            "required": ["releaseId", "authorId", "roles"],
            "additionalProperties": false
        }
        JSON;

    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $releaseId,
        public int $authorId,
        public array $roles,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            releaseId: $arguments->id('releaseId'),
            authorId: $arguments->id('authorId'),
            roles: $arguments->texts('roles') ?? [],
        );
    }
}
