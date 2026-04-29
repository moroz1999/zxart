<?php

class structureSkipIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return false;
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereNotIn('id', (array)$argument);
        return $query;
    }
}