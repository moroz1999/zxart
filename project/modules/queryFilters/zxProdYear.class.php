<?php

class zxProdYearQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        if (in_array('this', $argument)) {
/c            // In January the current year is nearly empty, so the previous year is included too.
            $argument = date('n') === '1' ? [date('Y') - 1, (int)date('Y')] : [(int)date('Y')];
        }
        $query->whereIn($this->getTable() . '.year', $argument);

        return $query;
    }
}