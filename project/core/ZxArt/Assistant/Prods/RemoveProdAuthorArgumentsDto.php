<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `remove_prod_author`.
 */
final readonly class RemoveProdAuthorArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "prodId": {"type": "integer", "description": "the production"},
                "authorId": {"type": "integer", "description": "the credited author or author alias"}
            },
            "required": ["prodId", "authorId"],
            "additionalProperties": false
        }
        JSON;

    public function __construct(
        public int $prodId,
        public int $authorId,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            prodId: $arguments->id('prodId'),
            authorId: $arguments->id('authorId'),
        );
    }
}
