<?php

class zxMusicFormatGroupQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $query->whereIn($this->getTable() . '.formatGroup', $argument);
        } else {
            $query->where($this->getTable() . '.formatGroup', '=', $argument);
        }

        return $query;
    }
}