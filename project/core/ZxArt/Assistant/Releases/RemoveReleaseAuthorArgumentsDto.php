<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `remove_release_author`.
 */
final readonly class RemoveReleaseAuthorArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "releaseId": {"type": "integer", "description": "the release"},
                "authorId": {"type": "integer", "description": "the credited author or author alias"}
            },
            "required": ["releaseId", "authorId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $releaseId,
        public int $authorId,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            releaseId: $arguments->id('releaseId'),
            authorId: $arguments->id('authorId'),
        );
    }
}
