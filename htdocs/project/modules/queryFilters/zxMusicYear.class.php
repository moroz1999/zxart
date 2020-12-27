<?php

class zxMusicYearQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('year', $argument);
        return $query;
    }
}