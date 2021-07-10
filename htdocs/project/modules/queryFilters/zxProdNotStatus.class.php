<?php

class zxProdNotStatusQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        $query->whereNotIn($this->getTable() . '.legalStatus', $argument);

        return $query;
    }
}