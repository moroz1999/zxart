<?php

class groupCityQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'group';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('city', $argument);
        return $query;
    }
}