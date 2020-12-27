<?php

class zxProdAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'zxProd';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    public function getFilteredIdList($argument, $query)
    {
        $query = parent::getFilteredIdList($argument, $query);
        $query->whereNotIn(
            'id',
            function ($subQuery) {
                $subQuery->from('structure_links')->select('childStructureId')->where('parentStructureId', '=', 92171);
            }
        );

        return $query;
    }
}