<?php

class zxProdCategoryIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProdCategory';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn($this->getTable() . '.id', (array)$argument);
        return $query;
    }
}