<?php

class authorAllQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'author';
    }

    public function getFilteredIdList($argument, $query)
    {
        return $query;
    }
}