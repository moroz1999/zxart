<?php

class zxMusicIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('id', (array)$argument);
        return $query;
    }
}