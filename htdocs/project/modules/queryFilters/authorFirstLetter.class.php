<?php

class authorFirstLetterQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'author';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $query->where('title', 'like', reset($argument) . '%');
        } else {
            $query->where('title', 'like', $argument . '%');
        }
        return $query;
    }
}