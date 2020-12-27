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
            $query->whereIn('compo', $argument);
        } else {
            $query->where('compo', '=', $argument);
        }

        return $query;
    }
}