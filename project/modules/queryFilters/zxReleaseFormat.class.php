<?php

class zxReleaseFormatQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxRelease';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn($this->getTable() . '.id', function ($subQuery) use ($argument) {
            $subQuery->from('module_zxrelease_format')->select('elementId')->whereIn('value', $argument);
        });
        return $query;
    }
}