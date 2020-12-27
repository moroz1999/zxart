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
        $query->whereIn('id', $argument);
        return $query;
    }
}