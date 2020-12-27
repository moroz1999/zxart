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
            $query->whereIn('formatGroup', $argument);
        } else {
            $query->where('formatGroup', '=', $argument);
        }

        return $query;
    }
}