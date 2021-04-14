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
//        $countries = $db->table('module_country')
//            ->whereIn('id', function ($countriesQuery) use ($query) {
//                $countriesQuery->from('module_group')->select('country')->whereIn('id', function ($producersQuery) use ($query) {
//                    $producersQuery->from('structure_links')->whereIn('childStructureId', $query)->where('type', '=', 'zxProdGroups')->select('parentStructureId');
//                });
//            })
//            ->select('title', 'id')
//            ->orderBy('title', 'asc')
//            ->where('languageId', '=', $languageId)
//            ->get();
        return $query;
    }
}