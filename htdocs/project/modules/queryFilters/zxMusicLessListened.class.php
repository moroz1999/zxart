<?php

class zxMusicLessListenedQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->where('plays', '>', 0);
        return $query;
    }
}