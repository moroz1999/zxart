<?php

class zxPictureTypeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
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