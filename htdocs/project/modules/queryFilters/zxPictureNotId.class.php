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
        $query->whereNotIn($this->getTable() . '.id', $argument);
        return $query;
    }
}