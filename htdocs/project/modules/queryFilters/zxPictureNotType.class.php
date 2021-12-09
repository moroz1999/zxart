<?php

class zxPictureNotTypeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $query->whereNotIn($this->getTable() . '.type', $argument);
        } else {
            $query->where($this->getTable() . '.type', '!=', $argument);
        }
        return $query;
    }
}