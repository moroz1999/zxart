<?php

class zxPictureIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('id', (array)$argument);
        return $query;
    }
}