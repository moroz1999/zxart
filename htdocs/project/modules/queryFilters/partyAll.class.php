<?php

class partyAllQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'party';
    }

    public function getFilteredIdList($argument, $query)
    {
        return $query;
    }
}