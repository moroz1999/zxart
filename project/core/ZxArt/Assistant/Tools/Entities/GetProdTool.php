<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Entities;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Prods\Dto\ProdCardDto;
use ZxArt\Prods\Services\ProdCardReader;

/**
 * @implements ReadTool<GetEntityRequestDto>
 *
 * @psalm-api
 */
final readonly class GetProdTool implements ReadTool
{
    public function __construct(
        private ProdCardReader $prodCardReader,
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
        return 'Reads one production: every field as update_prod takes it, the titles of the entities the fields '
            . 'name by id, the credited authors with roles, the releases, the files of each list with their ids, '
            . 'and the series and compilations it is part of.';
    }

    #[Override]
    public function requestClass(): string
    {
        return GetEntityRequestDto::class;
    }

    #[Override]
    public function execute(object $request): ProdCardDto
    {
        return $this->prodCardReader->read($request->id);
    }
}
