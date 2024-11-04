<?php


use Illuminate\Database\Query\Builder;

class ReleasesResolver
{
    public function __construct(
        protected ProdsManager $prodsManager,
    )
    {
    }

    public function getElementsByQuery(?Builder $query, ?array $sort = [], ?int $start = null, ?int $amount = null): array
    {
        return $this->prodsManager->getReleasesByIdList($query, $sort, $start, $amount);
    }

    public function makeQuery(): Builder
    {
        return $this->prodsManager->makeReleasesQuery();
    }
}