<?php

class authorCityQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'author';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('city', $argument);
        return $query;
    }
}