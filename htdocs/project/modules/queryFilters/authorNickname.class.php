<?php

class authorNicknameQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'author';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('title', $argument);

        return $query;
    }
}