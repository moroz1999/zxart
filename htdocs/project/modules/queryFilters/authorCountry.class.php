<?php

class authorCountryQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'author';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('country', $argument);
        return $query;
    }
}