<?php

class partyCityQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'party';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('city', $argument);
        return $query;
    }
}