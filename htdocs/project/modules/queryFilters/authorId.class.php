<?php

class authorIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'author';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        $query->whereIn('id', $argument);
        return $query;
    }
}