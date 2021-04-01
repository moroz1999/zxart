<?php

class zxProdFirstLetterQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $query->where('title', 'like', reset($argument) . '%');
        } else {
            if (preg_match('/[a-zA-Z]/', $argument)) {
                $query->where('title', 'like', $argument . '%');
            } else {
                $query->whereRaw("title NOT RLIKE '^[A-Z]'");
            }
        }
        return $query;
    }
}