<?php

class zxReleaseLanguageQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxRelease';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn($this->getTable() . '.id', function ($subQuery) use ($argument) {
            $subQuery->from('zxitem_language')->select('elementId')->whereIn('value', $argument);
        });
        return $query;
    }
}