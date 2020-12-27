<?php

class zxMusicGameQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->where('game', '!=', '0');
        return $query;
    }
}