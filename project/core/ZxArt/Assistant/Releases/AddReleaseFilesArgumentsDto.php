<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Releases\ReleaseFileKind;

/**
 * The arguments of `add_release_files`.
 */
final readonly class AddReleaseFilesArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "releaseId": {"type": "integer", "description": "the release"},
                "kind": {"type": "string", "description": "the file list to add to", "enum": ["screenshots", "inlays", "info", "ads"]},
                "urls": {"type": "array", "items": {"type": "string"}, "description": "http or https addresses of the files"}
            },
            "required": ["releaseId", "kind", "urls"],
            "additionalProperties": false
        }
        JSON;

    /**
     * @param list<string> $urls
     */
    public function __construct(
        public int $releaseId,
        public ReleaseFileKind $kind,
        public array $urls,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            releaseId: $arguments->id('releaseId'),
            kind: ReleaseFileKind::from($arguments->oneOf('kind', array_column(ReleaseFileKind::cases(), 'value')) ?? ''),
            urls: $arguments->texts('urls') ?? [],
        );
    }
}
