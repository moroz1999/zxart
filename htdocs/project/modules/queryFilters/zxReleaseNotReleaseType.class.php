<?php

class zxReleaseNotReleaseTypeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxRelease';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn($this->getTable() . '.id', function ($subQuery) use ($argument) {
            $subQuery->from('module_zxrelease')->select('id')->whereNotIn('releaseType', $argument);
        });
        return $query;
    }
}