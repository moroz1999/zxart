<?php

class zxMusicGameQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->where($this->getTable() . '.game', '!=', '0');
        return $query;
    }
}