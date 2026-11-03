<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `delete_prod_file`.
 */
final readonly class DeleteProdFileArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "prodId": {"type": "integer", "description": "the production"},
                "fileId": {"type": "integer", "description": "the file, as get_prod lists it"}
            },
            "required": ["prodId", "fileId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $prodId,
        public int $fileId,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            prodId: $arguments->id('prodId'),
            fileId: $arguments->id('fileId'),
        );
    }
}
