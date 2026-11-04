<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Web;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Releases\ItchIo\ItchIoGameUrl;

/**
 * The arguments of `list_itch_io_files`.
 */
final readonly class ListItchIoFilesArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "url": {"type": "string", "description": "the address of an itch.io game, https://<creator>.itch.io/<game>, or of any of its pages"}
            },
            "required": ["url"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public ItchIoGameUrl $game,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        $url = $arguments->requiredText('url');

        return new self(ItchIoGameUrl::tryFrom($url) ?? throw new ToolArgumentsException("Not the address of an itch.io game: $url"));
    }
}
