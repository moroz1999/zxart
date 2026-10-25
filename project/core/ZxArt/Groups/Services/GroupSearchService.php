<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use ZxArt\Groups\Dbal\GroupSearchDbal;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Shared\Dto\IdPageDto;

/**
 * Groups by typed filters. A nested software filter runs through the
 * production search, and the groups are narrowed by the productions it finds.
 *
 * @psalm-api
 */
final readonly class GroupSearchService
{
    public function __construct(
        private GroupSearchDbal $groupSearchDbal,
        private ProdSearchService $prodSearchService,
    ) {
    }

    public function search(GroupSearchFilterDto $filter, int $start, int $limit): IdPageDto
    {
        $foundProdIds = null;
        if ($filter->prods !== null) {
            $foundProdIds = $this->prodSearchService->idsQuery($filter->prods);
            if ($foundProdIds === null) {
                return new IdPageDto(total: 0, ids: []);
            }
        }

        return new IdPageDto(
            total: $this->groupSearchDbal->count($filter, $foundProdIds),
            ids: $this->groupSearchDbal->findIds($filter, $foundProdIds, $start, $limit),
        );
    }
}
