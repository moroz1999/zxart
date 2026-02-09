<?php

class zxMusicLessListenedQueryFilter extends QueryFilter
{
    private const int MAX_PLAYS = 10;

    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->where($this->getTable() . '.plays', '<', self::MAX_PLAYS);
        return $query;
    }
}
