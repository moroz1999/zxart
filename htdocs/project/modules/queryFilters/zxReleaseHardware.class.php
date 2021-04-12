<?php

class zxReleaseHardwareQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxRelease';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn($this->getTable() . '.id', function ($subQuery) use ($argument) {
            $subQuery->from('module_zxrelease_hw_required')->select('elementId')->whereIn('value', $argument);
        });
        return $query;
    }
}