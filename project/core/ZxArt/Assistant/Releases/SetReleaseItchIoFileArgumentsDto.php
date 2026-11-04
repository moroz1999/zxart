<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Releases\ItchIo\ItchIoGameUrl;

/**
 * The arguments of `set_release_itch_io_file`.
 */
final readonly class SetReleaseItchIoFileArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "releaseId": {"type": "integer", "description": "the release"},
                "url": {"type": "string", "description": "the address of the itch.io game, https://<creator>.itch.io/<game>"},
                "uploadId": {"type": "integer", "description": "the file of the game, as list_itch_io_files gives it"}
            },
            "required": ["releaseId", "url", "uploadId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $releaseId,
        public ItchIoGameUrl $game,
        public int $uploadId,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        $url = $arguments->requiredText('url');

        return new self(
            releaseId: $arguments->id('releaseId'),
            game: ItchIoGameUrl::tryFrom($url) ?? throw new ToolArgumentsException("Not the address of an itch.io game: $url"),
            uploadId: $arguments->id('uploadId'),
        );
    }
}
