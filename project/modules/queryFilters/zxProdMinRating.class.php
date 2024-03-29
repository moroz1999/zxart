<?php

class zxProdMinRatingQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        if (is_array($argument)) {
            $argument = reset($argument);
        }
        $query->where($this->getTable() . '.votes', '>=', $argument);

        return $query;
    }
}