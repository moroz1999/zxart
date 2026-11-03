<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `delete_release_file`.
 */
final readonly class DeleteReleaseFileArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "releaseId": {"type": "integer", "description": "the release"},
                "fileId": {"type": "integer", "description": "the file, as get_release lists it"}
            },
            "required": ["releaseId", "fileId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $releaseId,
        public int $fileId,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            releaseId: $arguments->id('releaseId'),
            fileId: $arguments->id('fileId'),
        );
    }
}
