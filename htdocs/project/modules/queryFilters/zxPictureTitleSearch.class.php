<?php

class zxPictureTitleSearchQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxPicture';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $argument = reset($argument);
        }
        $query->where($this->getTable() . '.title', 'like', '%' . $argument . '%');

        return $query;
    }
}