<?php

abstract class QueryFilter extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    /**
     * return type of query converter required to provide the right query for the filter
     *
     * @return string|boolean
     */
    abstract public function getRequiredType();

    /**
     * @param mixed $argument
     * @param Illuminate\Database\Query\Builder $query
     * @return mixed
     */
    abstract public function getFilteredIdList($argument, $query);

    protected function getTable()
    {
        return 'module_' . strtolower($this->getRequiredType());
    }
}
