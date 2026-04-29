<?php

use Illuminate\Database\Query\Builder;

class ApiQuery extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    protected $filtrationParameters = [];
    protected $resultTypes = [];

    /**
     * @return array
     */
    public function getResultTypes()
    {
        return $this->resultTypes;
    }

    protected $filterQueries;
    protected $queryResult = false;
    protected $exportType;
    protected $limit;
    protected $order;
    protected $start;
    protected $optimized = true;

    /**
     * @param boolean $optimized
     */
    public function setOptimized($optimized)
    {
        $this->optimized = $optimized;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param $exportType
     * @return $this
     */
    public function setExportType($exportType)
    {
        unset($this->filterQueries);
        $this->exportType = $exportType;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @param $parameters
     * @return $this
     */
    public function setFiltrationParameters($parameters): ApiQuery
    {
        $this->filtrationParameters = $parameters;
        return $this;
    }

    public function setResultTypes($types)
    {
        $this->resultTypes = $types;
        return $this;
    }

    protected function performFiltration()
    {
        if (!$this->resultTypes) {
            $this->resultTypes = [$this->exportType];
        }
        /**
         * @var QueryFiltersManager $queryFiltersManager
         */
        $queryFiltersManager = $this->getService(QueryFiltersManager::class);
        $this->filterQueries = $queryFiltersManager->getFilterQueries(
            $this->filtrationParameters,
            $this->resultTypes,
            $this->optimized
        );

        return $this->filterQueries;
    }

    public function getExportFilteredQuery(): ?Builder
    {
        if ($filterQueries = $this->getFilterQueries(true)) {
            return $filterQueries[$this->exportType];
        }
        return null;
    }

    public function getFilterQueries($forceUpdate = false)
    {
        if (!isset($this->filterQueries) || $forceUpdate) {
            $this->performFiltration();
        }
        return $this->filterQueries;
    }

    public function getQueryResult()
    {
        if (!$this->queryResult) {
            $filterQueries = $this->getFilterQueries();
            /**
             * @var ApiQueryResultResolver $apiQueryResultResolver
             */
            $apiQueryResultResolver = $this->getService(ApiQueryResultResolver::class);
            $this->queryResult = $apiQueryResultResolver->resolve(
                $filterQueries,
                $this->exportType,
                $this->resultTypes,
                $this->order,
                $this->start,
                $this->limit
            );
            $this->queryResult['start'] = $this->start;
            $this->queryResult['limit'] = $this->limit;
        }
        return $this->queryResult;
    }
}