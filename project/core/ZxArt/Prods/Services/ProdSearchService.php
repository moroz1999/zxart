<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use ApiQueriesManager;
use Illuminate\Database\Query\Builder;
use ZxArt\Prods\Dbal\ProdSearchDbal;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Shared\Dto\IdPageDto;
use ZxArt\Shared\StructureType;

/**
 * Productions by typed filters. The filters the catalogue offers run through
 * the catalogue's own query filters, so they match what its lists show; the
 * rest (title, dates, people, places, parties, series) narrow the result
 * after, in `ProdSearchDbal`.
 *
 * @psalm-api
 */
final readonly class ProdSearchService
{
    public function __construct(
        private ApiQueriesManager $apiQueriesManager,
        private ProdSearchDbal $prodSearchDbal,
    ) {
    }

    public function search(ProdSearchFilterDto $filter, int $start, int $limit): IdPageDto
    {
        $catalogueIds = $this->catalogueIds($filter);
        if ($catalogueIds === null) {
            return new IdPageDto(total: 0, ids: []);
        }

        return new IdPageDto(
            total: $this->prodSearchDbal->count($catalogueIds, $filter),
            ids: $this->prodSearchDbal->findIds($catalogueIds, $filter, $start, $limit),
        );
    }

    /**
     * The ids of the productions the filter finds, as a query of rows with an
     * `id`, for a search of authors or groups to narrow by; `null` when none
     * can match.
     */
    public function idsQuery(ProdSearchFilterDto $filter): ?Builder
    {
        $catalogueIds = $this->catalogueIds($filter);

        return $catalogueIds !== null ? $this->prodSearchDbal->idsQuery($catalogueIds, $filter) : null;
    }

    private function catalogueIds(ProdSearchFilterDto $filter): ?Builder
    {
        $apiQuery = $this->apiQueriesManager->getQuery();
        $apiQuery->setExportType(StructureType::ZxProd->value);
        $apiQuery->setFiltrationParameters($this->catalogueFilters($filter));
        // a search may follow a write in the same request
        $apiQuery->setTemporaryTables(false);

        return $apiQuery->getExportFilteredQuery();
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogueFilters(ProdSearchFilterDto $filter): array
    {
        $filters = array_filter([
            'zxProdCategory' => $filter->categoryIds,
            'zxProdHardware' => $filter->hardware,
            'zxProdLanguage' => $filter->languages,
            'zxProdStatus' => array_map(static fn(LegalStatus $status): string => $status->name, $filter->legalStatuses),
            'zxReleaseReleaseType' => array_map(static fn(ReleaseTypes $type): string => $type->value, $filter->releaseTypes),
            'zxReleaseFormat' => $filter->formats,
            'zxProdTagsInclude' => $filter->tagIds,
        ], static fn(array $values): bool => $values !== []);

        return $filters !== [] ? $filters : ['zxProdAll' => true];
    }
}
