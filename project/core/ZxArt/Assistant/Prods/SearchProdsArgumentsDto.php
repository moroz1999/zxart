<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\PageArguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Prods\Dto\ProdSearchFilterDto;

/**
 * The arguments of `search_prods`: the filter of the productions, flat,
 * beside the page.
 */
final readonly class SearchProdsArgumentsDto
{
    public const string SCHEMA = '{"type": "object", "properties": {' . ProdFilterArguments::PROPERTIES . ', '
        . PageArguments::PROPERTIES . '}, "additionalProperties": false}';

    public function __construct(
        public ProdSearchFilterDto $filter,
        public PageArguments $page,
    ) {
    }

    /**
     * @throws ToolArgumentsException
     */
    public static function from(Arguments $arguments): self
    {
        return new self(
            filter: ProdFilterArguments::filter($arguments),
            page: PageArguments::from($arguments),
        );
    }
}
