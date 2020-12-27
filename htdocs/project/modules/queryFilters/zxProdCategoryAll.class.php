<?php

class zxProdCategoryAllQueryFilter extends QueryFilter
{

    public function getRequiredType()
    {
        return 'zxProdCategory';
    }

    public function getFilteredIdList($argument, $query)
    {
        return $query;
    }
}