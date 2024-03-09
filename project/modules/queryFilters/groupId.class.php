<?php

class groupIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'group';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        $query->whereIn($this->getTable() . '.id', $argument);
        return $query;
    }
}