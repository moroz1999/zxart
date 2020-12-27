<?php

class partyCountryQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'party';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('country', $argument);
        return $query;
    }
}