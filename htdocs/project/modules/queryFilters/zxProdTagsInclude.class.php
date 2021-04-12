<?php

class zxProdTagsIncludeQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $query->whereIn('id', function ($subQuery) use ($argument) {
            $subQuery->from('structure_links')
                ->whereIn('parentStructureId', (array)$argument)
                ->where('type', '=', 'tagLink')
                ->select('childStructureId');
        });
        return $query;

    }
}