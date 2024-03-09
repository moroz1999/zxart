<?php

class zxMusicCompoQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxMusic';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $query->whereIn($this->getTable() . '.compo', $argument);
        } else {
            $query->where($this->getTable() . '.compo', '=', $argument);
        }

        return $query;
    }
}