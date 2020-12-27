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
            $query->whereIn('type', $argument);
        } else {
            $query->where('type', '=', $argument);
        }

        return $query;
    }
}