<?php

class zxPictureYearQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        $query->whereIn('year', $argument);

        return $query;
    }
}