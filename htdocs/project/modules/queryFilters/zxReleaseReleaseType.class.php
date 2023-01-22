<?php

class zxReleaseReleaseTypeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxRelease';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn($this->getTable() . '.id', function ($subQuery) use ($argument) {
            $subQuery->from('module_zxrelease')->select('id')->whereIn('releaseType', $argument);
        });
        return $query;
    }
}