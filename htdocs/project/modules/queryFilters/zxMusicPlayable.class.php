<?php

class zxMusicPlayableQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->where('mp3Name', '!=', '');
        return $query;
    }
}