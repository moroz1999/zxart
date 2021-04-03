<?php

class groupAliasIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'groupAlias';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        $query->whereIn($this->getTable() . '.id', $argument);
        return $query;
    }
}