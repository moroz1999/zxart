<?php

declare(strict_types=1);

namespace ZxArt\Authors\Services;

use ZxArt\Authors\Dbal\AuthorSearchDbal;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Shared\Dto\IdPageDto;

/**
 * Authors by typed filters. A nested software filter runs through the
 * production search, and the authors are narrowed by the productions it finds.
 *
 * @psalm-api
 */
final readonly class AuthorSearchService
{
    public function __construct(
        private AuthorSearchDbal $authorSearchDbal,
        private ProdSearchService $prodSearchService,
    ) {
    }

    public function search(AuthorSearchFilterDto $filter, int $start, int $limit): IdPageDto
    {
        $foundProdIds = null;
        if ($filter->prods !== null) {
            $foundProdIds = $this->prodSearchService->idsQuery($filter->prods);
            if ($foundProdIds === null) {
                return new IdPageDto(total: 0, ids: []);
            }
        }

        return new IdPageDto(
            total: $this->authorSearchDbal->count($filter, $foundProdIds),
            ids: $this->authorSearchDbal->findIds($filter, $foundProdIds, $start, $limit),
        );
    }
}
