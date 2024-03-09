<?php

class zxMusicPlayableQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->where($this->getTable() . '.mp3Name', '!=', '');
        return $query;
    }
}