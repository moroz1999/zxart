<?php

class zxReleaseIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxRelease';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('id', (array)$argument);
        return $query;
    }
}