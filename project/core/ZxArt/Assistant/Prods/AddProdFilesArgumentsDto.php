<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Prods\ProdFileKind;

/**
 * The arguments of `add_prod_files`.
 */
final readonly class AddProdFilesArgumentsDto
{
    public const string SCHEMA = <<<'JSON'
        {
            "type": "object",
            "properties": {
                "prodId": {"type": "integer", "description": "the production"},
                "kind": {"type": "string", "description": "the file list to add to", "enum": ["screenshots", "inlays", "maps", "rzx"]},
                "urls": {"type": "array", "items": {"type": "string"}, "description": "http or https addresses of the files"}
            },
            "required": ["prodId", "kind", "urls"],
            "additionalProperties": false
        }
        JSON;

    /**
     * @param list<string> $urls
     */
    public function __construct(
        public int $prodId,
        public ProdFileKind $kind,
        public array $urls,
    ) {
    }

    public static function from(Arguments $arguments): self
    {
        return new self(
            prodId: $arguments->id('prodId'),
            kind: ProdFileKind::from($arguments->oneOf('kind', array_column(ProdFileKind::cases(), 'value')) ?? ''),
            urls: $arguments->texts('urls') ?? [],
        );
    }
}
