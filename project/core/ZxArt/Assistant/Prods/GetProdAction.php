<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\ReadAction;

/**
 * Reads one production.
 *
 * @psalm-api
 */
final readonly class GetProdAction implements ReadAction
{
    public function __construct(
        private ProdDtos $prodDtos,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'get_prod';
    }

    #[Override]
    public function description(): string
    {
        return 'Reads one production: its fields as update_prod takes them, the titles of the entities its ids name, its '
            . 'authors with roles, its releases, its files, and the series and compilations that include it.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): ProdDto
    {
        return $this->prodDtos->prod(IdArgumentsDto::from($arguments)->id);
    }
}
