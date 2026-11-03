<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Assistant\Actions\Arguments;

/**
 * The arguments of `set_prod_author`.
 */
final readonly class SetProdAuthorArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "prodId": {"type": "integer", "description": "the production"},
                "authorId": {"type": "integer", "description": "an author or an author alias"},
                "roles": {"type": "array", "items": {"type": "string"}, "description": "roles from list_options; they replace the author's roles, none stands for unknown"}
            },
            "required": ["prodId", "authorId", "roles"],
            "additionalProperties": false
        }
        JSON;

    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $prodId,
        public int $authorId,
        public array $roles,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            prodId: $arguments->id('prodId'),
            authorId: $arguments->id('authorId'),
            roles: $arguments->texts('roles') ?? [],
        );
    }
}
