<?php

class zxPictureNotIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        $query->whereNotIn('id', $argument);
        return $query;
    }
}