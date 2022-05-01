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
        if (in_array('this', $argument)) {
            $argument = [date('Y'), date('Y') - 1];
        }
        $query->whereIn($this->getTable() . '.year', $argument);

        return $query;
    }
}