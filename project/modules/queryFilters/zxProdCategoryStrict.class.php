<?php

class zxProdCategoryStrictQueryFilter extends QueryFilter
{
    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        $ids = [];
        foreach ($argument as $categoryId) {
            $ids[] = $categoryId;
        }
        $query->whereIn(
            $this->getTable() . '.id',
            function ($subQuery) use ($ids) {
                $subQuery->from('structure_links')->select('structure_links.childStructureId')->where(
                    'structure_links.type',
                    '=',
                    'zxProdCategory'
                )->whereIn('structure_links.parentStructureId', $ids);
            }
        );

        return $query;
    }
}