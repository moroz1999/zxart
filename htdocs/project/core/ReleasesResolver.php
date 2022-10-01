<?php


use Illuminate\Database\Query\Builder;

class ReleasesResolver
{
    protected ProdsManager $prodsManager;

    public function setProdsManager(ProdsManager $prodsManager): void
    {
        $this->prodsManager = $prodsManager;
    }

    public function getElementsByQuery(?Builder $query, ?array $sort = [], ?int $start = null, ?int $amount = null)
    {
        return $this->prodsManager->getReleasesByIdList($query, $sort, $start, $amount);
    }

    public function makeQuery()
    {
        return $this->prodsManager->makeReleasesQuery();
    }
}