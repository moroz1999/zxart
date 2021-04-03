<?php

class partyIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'party';
    }

    public function getFilteredIdList($argument, $query)
    {
        return $query->whereIn($this->getTable() . '.id', (array)$argument);
    }
}