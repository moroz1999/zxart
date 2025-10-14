<?php


use Illuminate\Database\Query\Builder;
use ZxArt\Prods\Services\ProdsService;

class ReleasesResolver
{
    public function __construct(
        protected ProdsService $prodsService,
    )
    {
    }

    public function getElementsByQuery(?Builder $query, ?array $sort = [], ?int $start = null, ?int $amount = null): array
    {
        return $this->prodsService->getReleasesByIdList($query, $sort, $start, $amount);
    }

    public function makeQuery(): Builder
    {
        return $this->prodsService->makeReleasesQuery();
    }
}