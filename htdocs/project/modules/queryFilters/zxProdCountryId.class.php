<?php

class zxProdCountryIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn($this->getTable() . '.id', function ($producersQuery) use ($argument) {
            $producersQuery->from('structure_links')->where('type', '=', 'zxProdGroups')->select('childStructureId')->whereIn('parentStructureId', function ($countriesQuery) use ($argument) {
                $countriesQuery->from('module_group')->select('id')->whereIn('country', $argument);
            });
        });
        return $query;
    }
}