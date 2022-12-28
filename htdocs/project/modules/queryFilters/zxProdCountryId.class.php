<?php

class zxProdCountryIdQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn($this->getTable() . '.id', function ($subQuery) use ($argument) {
//            $subQuery2 = $this->getService('db')
//                ->table('authorship')
//                ->select('elementId')
//                ->whereIn('authorId', function ($authorsQuery) use ($argument) {
//                    $authorsQuery->from('module_author')
//                        ->select('id')
//                        ->whereIn('country', $argument);
//                });

            $subQuery->from('structure_links')
                ->where('type', '=', 'zxProdGroups')
                ->select('childStructureId')
                ->whereIn('parentStructureId', function ($countriesQuery) use ($argument) {
                    $countriesQuery->from('module_group')->select('id')->whereIn('country', $argument);
                })
            ;
        });

        return $query;
    }
}