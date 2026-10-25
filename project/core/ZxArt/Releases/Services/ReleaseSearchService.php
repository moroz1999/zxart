<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Releases\Dbal\ReleaseSearchDbal;
use ZxArt\Releases\Dto\ReleaseSearchFilterDto;
use ZxArt\Shared\Dto\IdPageDto;

/**
 * Releases by typed filters. A nested software filter runs through the
 * production search, and the releases are narrowed to the productions it
 * finds.
 *
 * @psalm-api
 */
final readonly class ReleaseSearchService
{
    public function __construct(
        private ReleaseSearchDbal $releaseSearchDbal,
        private ProdSearchService $prodSearchService,
    ) {
    }

    public function search(ReleaseSearchFilterDto $filter, int $start, int $limit): IdPageDto
    {
        $foundProdIds = null;
        if ($filter->prods !== null) {
            $foundProdIds = $this->prodSearchService->idsQuery($filter->prods);
            if ($foundProdIds === null) {
                return new IdPageDto(total: 0, ids: []);
            }
        }

        return new IdPageDto(
            total: $this->releaseSearchDbal->count($filter, $foundProdIds),
            ids: $this->releaseSearchDbal->findIds($filter, $foundProdIds, $start, $limit),
        );
    }
}
