<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use ApiQueriesManager;
use ZxArt\Prods\Dbal\ProdSearchRepository;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Shared\Dto\IdPageDto;
use ZxArt\Shared\StructureType;

/**
 * Productions by typed filters. The filters the catalogue offers run through
 * the catalogue's own query filters, so they match what its lists show; title,
 * years, author and group narrow the result after.
 *
 * @psalm-api
 */
final readonly class ProdSearchService
{
    public function __construct(
        private ApiQueriesManager $apiQueriesManager,
        private ProdSearchRepository $prodSearchRepository,
    ) {
    }

    public function search(ProdSearchFilterDto $filter, int $start, int $limit): IdPageDto
    {
        $apiQuery = $this->apiQueriesManager->getQuery();
        $apiQuery->setExportType(StructureType::ZxProd->value);
        $apiQuery->setFiltrationParameters($this->catalogueFilters($filter));
        // a search may follow a write in the same request
        $apiQuery->setTemporaryTables(false);
        $catalogueIds = $apiQuery->getExportFilteredQuery();
        if ($catalogueIds === null) {
            return new IdPageDto(total: 0, ids: []);
        }
        $title = $filter->title !== null ? trim($filter->title) : null;

        return new IdPageDto(
            total: $this->prodSearchRepository->count(
                $catalogueIds,
                $title,
                $filter->yearFrom,
                $filter->yearTo,
                $filter->authorId,
                $filter->groupId,
            ),
            ids: $this->prodSearchRepository->findIds(
                $catalogueIds,
                $title,
                $filter->yearFrom,
                $filter->yearTo,
                $filter->authorId,
                $filter->groupId,
                $start,
                $limit,
            ),
        );
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
            'zxProdCountryId' => $filter->countryIds,
            'zxProdTagsInclude' => $filter->tagIds,
        ], static fn(array $values): bool => $values !== []);

        return $filters !== [] ? $filters : ['zxProdAll' => true];
    }
}
