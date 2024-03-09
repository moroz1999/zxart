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
        $query->whereIn($this->getTable() . '.id', $argument);
        return $query;
    }
}