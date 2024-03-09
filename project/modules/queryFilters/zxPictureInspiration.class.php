<?php

class zxPictureInspirationQueryFilter extends QueryFilter
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
        if ($argument) {
            $query->where($this->getTable() . '.inspiredName', '!=', '');
        } else {
            $query->where($this->getTable() . '.inspiredName', '=', '');
        }
        return $query;
    }
}