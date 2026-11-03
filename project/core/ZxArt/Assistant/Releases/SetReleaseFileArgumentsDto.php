<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `set_release_file`.
 */
final readonly class SetReleaseFileArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "releaseId": {"type": "integer", "description": "the release"},
                "url": {"type": "string", "description": "an http or https address of the file"},
                "fileName": {"type": "string", "description": "the name to store it under; without it the name comes from the address"}
            },
            "required": ["releaseId", "url"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $releaseId,
        public string $url,
        public ?string $fileName = null,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            releaseId: $arguments->id('releaseId'),
            url: $arguments->requiredText('url'),
            fileName: $arguments->text('fileName'),
        );
    }
}
