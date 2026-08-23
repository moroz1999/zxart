<?php

class zxProdAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'zxProd';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'altTitle'];
    }

    public function getFilteredIdList($argument, $query)
    {
        $query = parent::getFilteredIdList($argument, $query);
        $query->whereNotIn(
            $this->getTable() . '.id',
            function ($subQuery) {
                $subQuery->from('structure_links')->select('structure_links.childStructureId')->where('structure_links.parentStructureId', '=', 92171);
            }
        );

        return $query;
    }
}