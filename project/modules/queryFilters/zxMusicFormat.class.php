<?php

class zxMusicFormatQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $query->whereIn($this->getTable() . '.type', $argument);
        } else {
            $query->where($this->getTable() . '.type', '=', $argument);
        }

        return $query;
    }
}