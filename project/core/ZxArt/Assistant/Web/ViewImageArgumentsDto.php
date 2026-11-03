<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Web;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;

/**
 * The arguments of `view_image`: an address or a file of the site, one of them.
 */
final readonly class ViewImageArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "url": {"type": "string", "description": "an http or https address of the image; give this or fileId"},
                "fileId": {"type": "integer", "description": "the id of a file on the site, as the get actions list it; give this or url"}
            },
            "required": [],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public ?string $url,
        public ?int $fileId,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        return new self(
            url: $arguments->text('url'),
            fileId: $arguments->number('fileId'),
        );
    }
}
