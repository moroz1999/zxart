<?php

class zxProdStatusQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        $query->whereIn($this->getTable() . '.legalStatus', $argument);

        return $query;
    }
}