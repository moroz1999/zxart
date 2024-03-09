<?php

class groupCountryQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'group';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn($this->getTable() . '.country', $argument);
        return $query;
    }
}